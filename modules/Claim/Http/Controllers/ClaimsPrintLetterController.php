<?php namespace Modules\Claim\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Claim\Entities\ProcessClaimsPrintLetter;
use Modules\Claim\Entities\Claim;
use Modules\Claim\Entities\ClaimFile;
use Modules\Claim\Entities\ClaimProcedure;
use Modules\Affiliate\Entities\AffiliatePolicy;
use App\ProcedureEntry;
use Modules\Email\Entities\EmailUtils;

class ClaimsPrintLetterController extends NovaController {

	protected $module_path='claims/printLetter';

    use \App\UploadAndDownloadFile;

	function __construct()
	{
        parent::__construct();
	}

	public function form($processId){
        try{
            $pup=ProcessClaimsPrintLetter::
                        findProcess($processId);
            if($pup==null){
                throw new \Exception("Proceso con ese id no existe", 1);
            }

            //get the list of claims associcate with this procedure
            $peid=$pup->procedure_entry_id;

            $claims=ProcedureEntry::find($peid)
                                    ->claims;

            $policy = $claims[0]->affiliatePolicy->policy;
            $client_name = $policy->customer->getFullNameAttribute();

            //get details of affiliate
            $documentType = \App\ProcedureDocument::pluck('description','id');
            $data=array();
            $index=0;

            $diagnosis= \App\Diagnosis::pluck('display_name','id');

            foreach ($claims as $claim) {
                $data[$index]['claim_id']=$claim->id;
                //name affiliate
                $affPolicy = $claim->affiliatePolicy;

                $data[$index]['client_name'] =  $client_name;

                $data[$index]['affiliate_name'] = $affPolicy->affiliate->full_name.
                                                    " - ".$affPolicy->affRole->name;
                //diagnosis
                $data[$index]['diagnosis'] = $diagnosis[$claim->diagnosis_id];

                $data[$index]['affiliate_id'] = $affPolicy->affiliate->pid_num;

                //url to print claim file
                $data[$index]['url_print_letter'] = $this->module_path."/$processId/printLetter/".$claim->id;

                //url to print claim form
                $data[$index]['url_print_form'] = $this->module_path."/$processId/printClaimForm/".$claim->id;

                $data[$index]['prevorders']=array();

                //claim_file
                $cfs=$claim->files()->where('invalid',0)->get();;
                $total=0;
                foreach ($cfs as $cf) {
                    //get detail of files
                    $data[$index]['files'][]=[
                            'id'          => $cf->file_entry_id,
                            'type'        => $documentType[$cf->procedure_document_id],
                            'description' => $cf->description,
                            'amount'      => $cf->amount,
                            'supplier'    => ($cf->supplier)?$cf->supplier->name:"",
                        ];
                    if($cf->prev_order==1){
                        $data[$index]['prevorders'][] = array("id"=>$cf->id,
                                                            "description"=>$cf->description);
                    }
                    $total+=$cf->amount;
                }
                $data[$index]['total']=$total;
                $index++;
            }

            $result['claims']        = $data;
            $result['policy_num']    = $policy->policy_number;
            $result['processID']     = $processId;

            $dataEmail = $this->getContentEmail($pup->procedureEntryRel);
            $result['emailto'] = $dataEmail['emailto'];
            $result['emailcc'] = $dataEmail['emailcc'];
            $result['emailcontent'] = $dataEmail['emailcontent'];

            $this->novaMessage->setData($result);
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
            return $this->returnJSONMessage(500);
        }
	}

	public function printLetter($processId,$claimId,Request $request){
		try{
			$pup=ProcessClaimsPrintLetter::
                        findProcess($processId);

            if($pup==null){
                throw new \Exception("Proceso con ese id no existe", 1);
            }

            $cl=Claim::with('affiliatePolicy')
                        ->findOrFail($claimId);

            $diagnosis= \App\Diagnosis::pluck('display_name','id');

            $policy = $cl->affiliatePolicy->policy;
            $client_name = $policy->customer->getFullNameAttribute();

            $summary=$cl->readableSummary();

            $claim['date']=(new \DateTime())->format('m-d-Y');
            $claim['num_claim']=$summary['id'];
            $claim['affiliate']=$summary['affiliate'];
            $claim['policy']=$policy->policy_number;
            $claim['client_name']=$client_name;
            $claim['patient']=$summary['affiliate'];
            $claim['diagnosis']=$diagnosis[$cl->diagnosis_id];

            $invoice_type = \App\ProcedureDocument::
                                where('name', 'claim_invoice')
                                ->value('id');

            $documentType = \App\ProcedureDocument::pluck('description','id');

            //claim_file
            $cfs=$cl->files()->where('invalid',0)->get();
            $total=0;
            $claim['listdocuments'] = $claim['invoices'] = array();
            foreach ($cfs as $cf) {
                if($invoice_type==$cf->procedure_document_id){
                    //get detail of files
                    //only when files are invioces
                    $claim['invoices'][]=[
                            'num_invoice' => $cf->description,
                            'provider'    => isset($cf->supplier)?$cf->supplier->name:"",
                            'amount'      => $cf->amount,
                        ];
                    $total=$total+$cf->amount;
                }else{
                    $claim['listdocuments'][]=[
                        'category'    => $documentType[$cf->procedure_document_id],
                        'description' => $cf->description,
                        'provider'    => isset($cf->supplier)?$cf->supplier->name:""
                    ];
                }
            }
            $claim['amount']=$total;

            $pdf = \PDF::loadView('claim::Claim_Letter', $claim);
            $localStrPath = \Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
            $file_path = $this->getCompanyDirectory()."/temp/claimletter_".$cl->id.".pdf";

            //check if file exist
            $exists = \Storage::disk('local')->exists($file_path);
            if($exists){
                \Storage::disk('local')->delete($file_path);
            }

            //save letter in a tmp locatiom
            $pdf->save($localStrPath.$file_path);
            $this->saveFileInDirectpry($file_path,
                                        $pup->procedure_entry_id,
                                        $cl->id,
                                        "Carta de Reclamo.pdf",
                                        "claim_letter");

            return $pdf->download('claim_letter.pdf');
    	}catch(\Exception $e){
    		\DB::rollback();
    		//show message error
    		$this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
    		return $this->returnJSONMessage(500);
    	}
	}

    private function saveFileInDirectpry($tmp_file_path,
                                        $procedure_entry_id,
                                        $claim_id,
                                        $nameFile,
                                        $description){
        $filename = $nameFile;
        $subfolder = "newclaims/".$procedure_entry_id;
        $path_file = $this->getCompanyDirectory().'/'.$subfolder;
        if(!\Storage::disk('local')->exists($path_file)){
            \Storage::makeDirectory($path_file);
        }
        $file_relative_driver = $path_file.'/'.$filename;

        $exists = \Storage::disk('local')->exists($file_relative_driver);
        if($exists){
            $fe = \App\FileEntry::where('description','claim_letter')
                                    ->where('table_type','claim')
                                    ->where('table_id',$claim_id)
                                    ->first();
            if($fe!=null){
                $fe->delete();
            }
            \Storage::disk('local')->delete($file_relative_driver);
        }

        \Storage::move($tmp_file_path, $file_relative_driver);
        $entry = new \App\FileEntry();
        $entry->mime = "application/pdf";
        $entry->original_filename = $nameFile;
        $entry->filename = $file_relative_driver;
        $entry->table_type = "claim";
        $entry->table_id = $claim_id;
        $entry->description = $description;
        $entry->driver = "local";
        $entry->complete_path = \Storage::disk('local')->getDriver()
                ->getAdapter()->getPathPrefix().$file_relative_driver;
        $entry->save();
    }

    public function printClaimForm($processId,$claimId,Request $request){
        try{
            $pup=ProcessClaimsPrintLetter::
                        findProcess($processId);

            if($pup==null){
                throw new \Exception("Proceso con ese id no existe", 1);
            }

            $cl = Claim::with('affiliatePolicy')
                        ->findOrFail($claimId);

            $claim = array();
            $claim['claim_id']=$cl->id;
            //name affiliate
            $affPolicy = $cl->affiliatePolicy;
            $policy = $cl->affiliatePolicy->policy;
            $client_name = $policy->customer->getFullNameAttribute();
            $affiliate = $affPolicy->affiliate;
            $claim['client_name'] =  $client_name;
            $claim['affiliate_name'] = $affiliate->full_name.
                                                " - ".$affPolicy->affRole->name;
            //diagnosis
            $claim['diagnosis'] = $cl->diagnosis->display_name;
            $claim['affiliate_id'] = $affiliate->pid_num;
            $claim['policy'] = $policy->policy_number;
            $claim['claim_date'] = $cl->created_at;
            $claim['aff_dob'] = date("m/d/Y",strtotime($affiliate->dob));


            $invoice_type = \App\ProcedureDocument::
                                where('name', 'claim_invoice')
                                ->value('id');

            $concept = \Modules\Claim\Entities\ClaimConcept::pluck('display_name', 'id');

            $currency = \App\Currency::pluck("display_name","id");

            //claim_file
            $cfs=$cl->files()->where('invalid',0)->get();
            $total=0;
            foreach ($cfs as $cf) {
                if($invoice_type==$cf->procedure_document_id){
                    //get detail of files
                    //only when files are invioces
                    $claim['invoices'][]=[
                            'num_invoice'  => $cf->description,
                            'date_invoice' => date("m/d/Y",strtotime($cf->date_invoice)),
                            'currency'     => $currency[$cf->currency_id],
                            'provider'     => isset($cf->supplier)?$cf->supplier->name:"",
                            'amount'       => $cf->amount,
                            'concept'      => $concept[$cf->concept]
                        ];
                    $total=$total+$cf->amount;
                }
            }
            $claim['total']=$total;

            $pdf = \PDF::loadView('claim::Claim_Form', $claim);
            $localStrPath = \Storage::disk('local')->getDriver()->getAdapter()->getPathPrefix();
            $file_path = $this->getCompanyDirectory()."/temp/claimform_".$cl->id.".pdf";

            //check if file exist
            $exists = \Storage::disk('local')->exists($file_path);
            if($exists){
                \Storage::disk('local')->delete($file_path);
            }

            //save form in a tmp locatiom
            $pdf->save($localStrPath.$file_path);
            $this->saveFileInDirectpry($file_path,
                                        $pup->procedure_entry_id,
                                        $cl->id,
                                        "Formulario de Reclamo.pdf",
                                        "claim_form");

            return $pdf->download('claim_Form.pdf');
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
            return $this->returnJSONMessage(500);
        }
    }

    private function getContentEmail(\App\ProcedureEntry $procedure){
        $policy = $procedure->policy;
        $data['policy_info'] = $policy->readableSummary();
        //get procedure claims
        $claimProcedures = ClaimProcedure::where('procedure_entry_id', $procedure->id)
                    ->get();
        if( count($claimProcedures) < 1 ){
            throw new \Exception('no claim files');
        }
        foreach($claimProcedures as $claimProcedure){
            $data['claims'][] = $claimProcedure->claim->readableSummary();
        }

        $details = '';
        foreach( $data['claims'] as $claim ){
            $details .= "Afiliado: ".$claim['affiliate']."\n";
            $details .= "Total: ".$claim['total']."\n";
            $details .= "Documentos enviados en la liquidación:\n";
            foreach( $claim['files'] as $file ){
                $details .=" * ".$file['type_name'].' | '.$file['filename'].' | '.$file['description'].' | monto: '.$file['amount'].' | '.$file['concept']."\n";
            }
            if( count($claim['invalidfiles'])>0 ){
                $details .="Documentos no válidos:\n";
                foreach ($claim['invalidfiles'] as $ifile) {
                    $details .= " * ".$file['type_name'].' | '.$file['filename'].' | '.$file['description'].' | monto: '.$file['amount']."\n";
                }
            }
            $details .= "\n";
        }

        $content = "Resumen de reclamos:\n";
        $content .= "   POLIZA #: ".$data['policy_info']['policy_number']."\n";
        $content .= "   TITULAR: ".$data['policy_info']['customer_name']."\n";
        $content .= "   PLAN: ".$data['policy_info']['plan_name']." / ".$data['policy_info']['deducible']."\n\n";
        $content .= "Detalle de reclamos por afiliado:\n\n$details";


        $emailConf['emailto'] = $data['policy_info']['customer_name']." <".$data['policy_info']['customer_email'].">";
        $emailConf['emailcc'] = "";
        $emailConf['emailcontent'] = $content;

        return $emailConf;
    }

    private function sendClaimsSummary(\App\ProcedureEntry $procedure, $emailSettings){
        $policy = $procedure->policy;

        $data['policy_info'] = $policy->readableSummary();

        //get procedure claims
        $claimProcedures = ClaimProcedure::where('procedure_entry_id', $procedure->id)
                    ->get();
        if( count($claimProcedures) < 1 ){
            throw new \Exception('no claim files');
        }

        foreach($claimProcedures as $claimProcedure){
            $data['claims'][] = $claimProcedure->claim->readableSummary();
        }

        //client
        $to['address'] = $data['policy_info']['customer_email'];
        $to['name'] = $data['policy_info']['customer_name'];

        //copy to the agent of policy
        $param['cc'] = $data['policy_info']['agente_email'];

        $param["variables"]['TRAMITE_ID'] = $procedure->id;
        $param["variables"]['AGENT_NAME'] = $data['policy_info']['agente_name'];

        $param["variables"]['CUSTOMER'] = $data['policy_info']['customer_name'];
        //plan / opcion deducible
        $param["variables"]['PLAN'] = $data['policy_info']['plan_name'];
        $param["variables"]['DEDUCIBLE'] = $data['policy_info']['deducible'];
        $param["variables"]['POLICY_NUMBER'] = $data['policy_info']['policy_number'];

        $param["to"] = EmailUtils::parseEmailField($emailSettings['to']);
        $param["cc"] = EmailUtils::parseEmailField($emailSettings['cc']);
        $param["template"] = $emailSettings['content'];

        $emailUtils=new EmailUtils();
        $emailUtils->sendEmailProcessMultiplesDestinataries('notifyClaimSent',$param);
    }

	public function process($process_ID,Request $request){
		\DB::beginTransaction();
        try{
            $pup=ProcessClaimsPrintLetter::
                        findProcess($process_ID);
            if($pup==null){
                throw new Exception("No existe proceso con ese ID", 1);
            }

            if($pup->state=='finished' ||
                $pup->state=='cancelled' ||
                $pup->state=='rellocated'){
                throw new Exception("Procese está ya completo", 1);
            }

            $pup->sendDocumentsReception($request);
            $pup->finish();

            $emailSettings['content'] = $request['emailcontent'];
            $emailSettings['to'] = $request['emailto'];
            $emailSettings['cc'] = $request['emailcc'];

            //send email with data about the claim
            $this->sendClaimsSummary($pup->procedureEntryRel,$emailSettings);

            \DB::commit();
            $this->novaMessage->setRoute(
                    'claim/pending');
            return $this->returnJSONMessage(200);
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(500);
        }
	}
}
