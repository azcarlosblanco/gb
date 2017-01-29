<?php namespace Modules\Claim\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Claim\Entities\ProcessClaimsReviewDocuments;
use Modules\Claim\Entities\Claim;
use Modules\Claim\Entities\ClaimFile;
use Modules\Claim\Entities\ClaimConcept;
use Modules\Claim\Entities\ClaimProcedure;
use Modules\Claim\Services\Validation\ClaimValidator;
use Modules\Affiliate\Entities\AffiliatePolicy;
use Modules\Supplier\Entities\Supplier;
use Modules\ClientService\Entities\Doctor;
use Modules\Email\Entities\EmailUtils;
use Modules\Plan\Entities\DeducibleOptions;
use App\UploadAndDownloadFile;

class ClaimsReviewDocsController extends NovaController {
  use \App\UploadAndDownloadFile;

  function __construct(){
    parent::__construct();
  }

  public function view($id){
    $table_type = 'procedure_entry';

    try{
      $process = ProcessClaimsReviewDocuments::findProcess($id);
      if( $process == null ){
        throw new \Exception('invalid claim');
      }

      if( !$process->isActive() ){
        //TODO process is finished, send claims resume
        throw new \Exception('invalid process');
      }

      $table_id = $process->procedure_entry_id;

      $policy = $process->procedureEntryRel->policy;
      if( is_null($policy) ){
        throw new \Exception('invalid policy');
      }

      $deducible = \Modules\Plan\Entities\Deducible::with('plan')
                  ->select('name','plan_id')
                  ->find($policy->plan_deducible_id);

      $data['policy_id'] = $policy->id;
      $data['policy_number'] = $policy->policy_number;
      $data['customer_name'] = $policy->customer->getFullNameAttribute();
      $data['effective_date'] = date("Y-m-d",strtotime($policy->start_date));
      $data['plan'] = $deducible->plan->name." ".$deducible->name;

      $files = \App\FileEntry::where('table_type', $table_type)
                        ->where('table_id', $process->procedure_entry_id)
                        ->select('id', 'original_filename', 'description', 'status', 'data')
                        ->get();

      if( is_null($files) ){
        throw new \Exception('no files');
      }

      foreach( $files as $i=>$obj ){
        $tmp_data = (array)json_decode($obj->data);
        $files[$i]->data = $tmp_data;
      }
      $data['files'] = $files;

      //policy affiliates list
      $affiliates = array();
      $aff_pol = AffiliatePolicy::where('policy_id', $policy->id)->get();

      if( is_null($aff_pol) ){
        throw new \Exception('no affiliates associated to policy');
      }

      foreach( $aff_pol as $ap ){
        $aff_name = $ap->affiliate->full_name;
        $aff_role = $ap->affRole->name;
        $affiliates[] = array('id'=>$ap->id, 'name'=>$aff_name, 'role'=>$aff_role);
      }

      $data['affiliates'] = $affiliates;

      $cid = \App\ProcedureCatalog::where('name','claims')->value('id');
      $data['categories'] = array();

      if(!empty($cid)){
        $data['categories'] = \App\ProcedureDocument::where('procedure_catalog_id', $cid)
        ->select('id', 'name', 'description')
        ->get();
      }

      $data['process_id'] = $id;
      $data['process_status'] = $process->state;

/*
* Added by Cesar Sulbaran
*/
       $data['suppliers'] = Supplier::select('id', 'name')
                                  ->get();
/************************/

      $data['concepts'] = \Modules\Claim\Entities\ClaimConcept::select('id', 'display_name')
                                    ->get();

      $data['currencies'] = \App\Currency::select('id', 'display_name')
                                    ->get();

      $data['diagnosis'] = \App\Diagnosis::select('id', 'display_name')
                                    ->get();

      //verify if it is first year
      $data['brand_new'] = ($policy->getYearsActive() > 0 ) ? 0 : 1;

      $this->novaMessage->setData($data);
      return $this->returnJSONMessage(200);
    }catch(\Exception $e){
      $this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
      return $this->returnJSONMessage(404);
    }
  }

  public function updateFiles(Request $request, $id){
    try{
      \DB::beginTransaction();

      $process = ProcessClaimsReviewDocuments::findProcess($id);
      if( $process == null ){
        throw new \Exception('invalid claim');
      }


      $fid = $request->input('id', 0);
      $description = $request->input('description', '');
      $data = $request->input('data',array());

      if( ! ( isset($data['ts']) ) ){
        throw new \Exception('invalid ts');
      }
      if( ! ( isset($data['supplier'])
              && \Modules\Supplier\Entities\Supplier::findOrFail($data['supplier']) ) ){
        throw new \Exception('invalid supplier');
      }

      if( !isset($data['procedure_document_id']) ){
        throw new \Exception('invalid type');
      }

      $type = \App\ProcedureDocument::find($data['procedure_document_id']);
      if($type==null){
        throw new \Exception('invalid type');
      }

      if($type->name=="claim_invoice"){
        //amount, currency, date, usa are compulsory
        if(!isset($data['currency'])){
          throw new \Exception('invalid currency');
        }

        if(!(isset($data['amount']) && preg_match("/^\d+(?:\.\d{2})?$/", $data['amount']))){
          throw new \Exception('invalid amount');
        }

        if(!isset($data['date'])){
          throw new \Exception('invalid date');
        }else{
          $data['date'] = date("Y-m-d",strtotime($data['date']));
        }

        if(!isset($data['usa'])){
          $data['usa']=0;
        }

        if($data['usa']!=0 && $data['usa']!=1){
          throw new \Exception('invalid usa');
        }
      }

      $valid = isset($data['valid'])?$data['valid']:"1";
      $reason_no_valid = "";
      if($valid==1){
        $reason_no_valid = isset($data['reason_no_valid'])?$data['reason_no_valid']:"";
      }


      $file_data = [
                  "ts" => $data['ts'],
                  "procedure_document_id" => $type->id."",
                  "supplier" => $data['supplier'],
                  "date" => isset($data['date'])?$data['date']:"",
                  "currency" => isset($data['currency'])?$data['currency']:"",
                  "amount" => isset($data['amount'])?$data['amount']:0,
                  "usa" => isset($data['usa'])?$data['usa']:"",
                  "valid" => $valid,
                  "reason_no_valid" => $reason_no_valid,
                   ];


      $params = array();
      $params['fieldname'] = 'file';
      $params['subfolder'] = 'newclaims/'.$process->procedure_entry_id;
      $params['table_type'] = 'procedure_entry';
      $params['table_id'] = $process->procedure_entry_id;;
      $params['data'] = json_encode($file_data);
      $params['multiple'] = false;
      $params['description'] = $description;

      try{
        if( $fid ){
          if( $request->file($params['fieldname']) !=null  ){
            //if get file id try to update
            $updated = $this->updateFile($request, $fid, $params);
            $uploadedFiles = array($updated);
            if( ! (is_array($uploadedFiles) && count($uploadedFiles)>0) ){
              throw new \Exception("Archivo no pudo ser subido");
            }
            $idfileUE = $uploadedFiles[0];
          }else{
            //update just the data section of fileentry
            $fe = \App\FileEntry::find($fid);
            $fe->description = $params['description'];
            $fe->data = $params['data'];
            $fe->save();
            $idfileUE = $fe->id;
          }
        }
        else{
          $uploadedFiles = $this->uploadFiles($request, $params);
          if( ! (is_array($uploadedFiles) && count($uploadedFiles)>0) ){
            throw new \Exception("Archivo no pudo ser subido");
          }
          $idfileUE = $uploadedFiles[0];
        }
      }catch(\Exception $e){
        throw $e;
      }



      \DB::commit();
      $this->novaMessage->setData(["id"=>$idfileUE]);
      return $this->returnJSONMessage(200);
    }catch( \Exception $e ){
      \DB::rollback();

      $this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
      return $this->returnJSONMessage(400);
    }
  }

  public function generateClaims(Request $request, $processID){
    try{
      $process = ProcessClaimsReviewDocuments::findProcess($processID);
      if( $process == null ){
        throw new \Exception('invalid claim');
      }

      if( !$process->isActive() ){
        throw new \Exception('invalid process');
      }

      $policy = $process->procedureEntryRel->policy;
      if( is_null($policy) || !$policy->isActive() ){
        throw new \Exception('invalid policy');
      }

      $invoice_type = \App\ProcedureDocument::where('name', 'claim_invoice')->value('id');
      $order_type = \App\ProcedureDocument::where('name', 'claim_laborder')->value('id');
      $result_type = \App\ProcedureDocument::where('name', 'claim_labresult')->value('id');

      if( empty($invoice_type) || empty($order_type)  || empty($result_type) ){
        throw new \Exception('document types do not exist');
      }

      $allowed_types = array($invoice_type, $order_type, $result_type);
      //$process_data = $request->input('data', '');
      $process_data = $request->getContent();
      $process_data = (array)json_decode($process_data);
      //validate input has correct format
      $affDiag = (array)$process_data['aff_diag'];
      ClaimValidator::validateGenerateClaimFormData($affDiag);


      \DB::beginTransaction();

      foreach( $affDiag as $id=>$diagnosis ){
        $diagnosis = (array)$diagnosis;
        $ap = AffiliatePolicy::findOrFail($id);
        //TODO validate effective date, dissmiss -> isActive()
        //procedure policy_id match
        if( $ap->policy_id != $policy->id ){
          throw new \Exception('affiliate policy does not match procedure');
        }

        foreach( $diagnosis as $diag_id => $file_list ){
          $file_list = (array)$file_list;
          $diag_obj = \App\Diagnosis::findOrFail($diag_id);

          $claim = new Claim();
          $claim->affiliate_policy_id = $id;
          $claim->diagnosis_id = $diag_id;
          $claim->status = 0;
          $claim->save();

          if( !isset($claim->id) ){
            throw new \Exception('error creating a claim');
          }

          $invoice_counter = 0;
          //create claim files
          foreach( $file_list as $x=>$fitem ){
            $fitem = (array)$fitem;

            $tmp_file = \App\FileEntry::findOrFail($fitem['file_entry_id']);
            $fdata = (array)json_decode($tmp_file->data);

            $fitem['concept'] = array_get($fitem, 'concept', 0);
            \Modules\Claim\Entities\ClaimConcept::findOrFail($fitem['concept']);
            //TODO validate supplier

            if( $fdata['procedure_document_id'] == $invoice_type ){
              $fitem['value'] = array_get($fitem, 'value', 0);

              if( empty($fitem['value']) || (!is_numeric($fitem['value']))){
                throw new \Exception('missing values for file affiliate association');
              }

              //TODO validate usa, date, currency

              //TODO: validate usa, date, currency
              $fitem['value'] = round(floatval($fitem['value']), 2);
              $invoice_counter++;
            }

            $cf = new ClaimFile();
            $cf->claim_id = $claim->id;
            $cf->description = $tmp_file->description;
            $cf->supplier_id = $fitem['supplier'];
            $cf->usa = array_get($fdata, 'usa', 0);
            $cf->procedure_document_id = $fdata['procedure_document_id'];
            $cf->file_entry_id = $fitem['file_entry_id'];
            $cf->date_invoice = array_get($fdata, 'date', NULL);
            $cf->amount = array_get($fitem, 'value', 0);
            $cf->concept = array_get($fitem, 'concept', NULL);
            $currency_id = array_get($fdata, 'currency', NULL);
            if($currency_id!=null && $currency_id!=""){
              $cf->currency_id = $currency_id;
            }
            $cf->prev_order = array_get($fitem, 'prev_order', 0);
            $cf->save();
          }//end files foreach

          if( $invoice_counter < 1 ){
            throw new \Exception('at least one invoice per claim needed');
          }

          //associate claim with procedure
          ClaimProcedure::create(['claim_id'=>$claim->id, 'procedure_entry_id'=>$process->procedureEntryRel->id]);
        }//end diagnosis foreach

      }//end affiliates foreach

      //finish the process
      $process->finish();

      \DB::commit();

      $this->novaMessage->setData("success");
      return $this->returnJSONMessage(200);
    }catch( \Exception $e ){
      \DB::rollback();
      $this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
      return $this->returnJSONMessage(404);
    }
  }//end generateClaims


  public function getPreviousOrders($id){
    try{
      //affiliate_policy id exists
      $ap = AffiliatePolicy::findOrFail($id);

      //list all
      $order_type = \App\ProcedureDocument::where('name', 'claim_laborder')->value('id');
      if( empty($order_type) ){
        throw new \Exception('document type does not exist');
      }

      //\DB::enableQueryLog();

      $data = ClaimFile::join('claim', 'claim_file.claim_id', '=', 'claim.id')
             ->where('claim_file.procedure_document_id', $order_type)
             ->where('claim.affiliate_policy_id', $id)
             ->select(\DB::raw('DISTINCT claim_file.file_entry_id as file_id'), 'claim_file.description as description', 'claim_file.supplier_id', 'claim_file.procedure_document_id as category', 'claim_file.concept')
             ->get();

      //TODO: for some reason DISTINCT IS NO WORKING IN LARAVEL, in future versions
      //when the bug is fixed delete this validation
      $files = array();
      $fileEntriesID = array();
      foreach ($data as $value) {
        if(!in_array($value->file_id, $fileEntriesID)){
          $files[]=$value;
          $fileEntriesID[]=$value->file_id;
        }
      }

      //print_r(json_encode(\DB::getQueryLog()));exit;
      $this->novaMessage->setData($files);
      return $this->returnJSONMessage(200);
    }catch(\Exception $e){
      $this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
      return $this->returnJSONMessage(404);
    }
  }
}
