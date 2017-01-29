<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use App\Http\Controllers\Nova\NovaController;
use Modules\Emission\Http\Requests\RegisterCustomerPaymentRequest;
use Modules\Emission\Entities\ProcessRegisterCustomerPayment;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Payment\Entities\BankAccountType;
use Modules\Payment\Entities\CreditCardType;
use Modules\Payment\Entities\CreditCardBrand;
use Modules\Payment\Entities\CreditCardWayPay;
use Modules\Policy\Entities\Policy;
use App\FileEntry;
use App\ProcessEntry;
use App\ProcessCatalog;
use Modules\Reception\Entities\RequestPolicyData;
use Modules\Plan\Entities\NumberPayments;
use App\ProcedureDocument;
use Modules\Payment\Services\Validation\PaymentValidator;
use Modules\Payment\Entities\ChequePaymentDetail;
use Modules\Payment\Entities\PolicyPaymentDetail;
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use Modules\Reception\Entities\ProcessSendCheckIC;

class  RegisterCustomerPaymentController extends NovaController {

    use UploadAndDownloadFile;
	
	function __construct()
	{
        parent::__construct();
	}

	public function form($process_ID){
        try{
            $pup=ProcessRegisterCustomerPayment::findProcess($process_ID);
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1); 
            }

            //policy data
            $policy_id = ProcessEntry::find($process_ID)
                                        ->procedureEntryRel()
                                        ->first()
                                        ->policy_id;
            $policy = Policy::with("planDeducible.plan")
                                ->find($policy_id);

            $catalog['payment_method'] = PaymentMethod::pluck('display','id');
            $catalog['payment_name'] = PaymentMethod::pluck('method','id');
            $catalog['account_type'] = BankAccountType::pluck('display_name','id');
            $catalog['credit_card_type'] = CreditCardType::pluck('display_name','id');
            $catalog['credit_card_brand'] = CreditCardBrand::pluck('display_name','id');
            $catalog['form_pay'] = CreditCardWayPay::pluck('display_name','id');
            $catalog['number_payments'] = NumberPayments::find($policy->payments_number_id)->description;
            $catalog['policy_number'] = $policy->policy_number;
            $catalog['client_name'] = $policy->customer->full_name;

            //get current information saved about payment
            $process_catalog_id = ProcessCatalog::where("name","UploadPolicyRequest")
                                            ->first()
                                            ->id;
            $processUpdateId = ProcessEntry::where("process_catalog_id",$process_catalog_id)
                                    ->where("procedure_entry_id",$pup->procedure_entry_id)
                                    ->first()
                                    ->id;
            $prev_data = (array)json_decode(
                                    RequestPolicyData::where('process_id', $processUpdateId)
                                            ->orderBy('id', 'desc')
                                            ->value('data')
                                        );
            $payment = (array)$prev_data['payment_obj'];
            $payment["payment_method_id"] = $payment["payment_method"];
            //get file uploaded
            $prdoc = ProcedureDocument::pluck("name","id");
            $prdocdesc = ProcedureDocument::pluck("description","id");

            $fe=FileEntry::where("table_type","procedure_entry")
                            ->where('table_id',$pup->procedure_entry_id)
                            ->get();
            $files = array();
            $index=0;
            foreach ($fe as $key => $file) {
                $dataf = (array)json_decode($file['data']);
                if(isset($dataf['procedure_document_id'])){
                    if($prdoc[$dataf['procedure_document_id']]=="creditcard-auth-form" || 
                        $prdoc[$dataf['procedure_document_id']]=="paycheck"){
                        $files[$index]['name']=$file['original_filename'];
                        $files[$index]['file_id']=$file['id'];
                        $files[$index]['description']=$prdocdesc[$dataf['procedure_document_id']];
                        $index++;
                    }
                }

                //editarlo para que quede correcto
                if(isset($dataf['process_id'])){
                    if($dataf['process_id']==$process_ID){
                        $files[$index]['name']=$file['original_filename'];
                        $files[$index]['file_id']=$file['id'];
                        $files[$index]['description']=$file['description'];
                        $index++;
                    }
                }   
            }

            //cost policy
            $costs = $policy->getPolicyCosts();
            $data['process_ID'] = $process_ID;
            $data['catalog'] = $catalog;
            $data['catalog']['costs'] = $costs;
            $data['payment_data'] = $payment;
            $data['payment_data']['files'] = $files;

            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

	public function registerPayment($process_ID,Request $request){
		\DB::beginTransaction();
        $code = null;
        try{
            $pup=ProcessRegisterCustomerPayment::findProcess($process_ID);
            if($pup==null){
                $code = 404;
                throw new \Exception("Proceso con ese ID no existe", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                $code = 400;
                throw new \Exception("El proceso ya se completo", 1);
            }

            $input = $request->all();

            $policy = $pup->procedureEntryRel->policy;
            $pq = $policy->getPolicyQuote(1);

            $input["policy_cost_id"] = $pq->id;
            
            //validar los datos
            PaymentValidator::validateRegisterPaymentFormData($input);

            //registrar el pago, con estado en espera de confirmacion de best doctors
            //el pago es para la primera cuota
            $input["payment_date"] = date("Y-m-d",strtotime($input["payment_date"]));
            $result = $pq->checkPayDateRestrictions("emision",$input["payment_date"]);
            if($result=="cancel_policy"){
                //TODO:CANCEL THE POLICY BECAUSE OF PAY IS AFTER 60 DAYS
                throw new \Exception("El pago no se puede efectuar porque han pasado más de 60 días desde la fecha de incio de la póliza");
            }

            $input['state']=PolicyPaymentDetail::S_WAIT_CONFIRMATION;
            $payment = $pq->registerPayment($input);

            $files = (array)json_decode($input['files']);

            foreach ($files as $file) {
                $file = (array)$file;
                if(isset($file['file_id'])){
                    $fileEntry = \App\FileEntry::find($file['file_id']);
                    $fileEntry->description = $file['description'];
                    $fileEntry->save();
                    \Modules\Payment\Entities\PaymentDetailFiles::create([
                                "table_type"    => $payment->getTable(),
                                "table_id"      => $payment->id,
                                "file_entry_id" => $file['file_id'],
                                    ]);      
                }
            }

            $pm=PaymentMethod::find($input['payment_method_id']);
            if($pm['method']=="cheque"){
                //create process to send cheque to best doctors
                $pro=new ProcessSendCheckIC();
                $pro->start($pup->procedureEntryRel);
                $pro->createSendDocument($payment->id);
            }

            $pup->sendPaymentInfo($policy,$payment,$pm['method']);

            $pup->finish();
            $this->novaMessage->setRoute('emission/pending');
            $this->novaMessage->addSuccesMessage("Pago","Se guardo la informacion de pago exitosamente");
            \DB::commit();
            $code = 200;
        }catch(\Exception $e){
            \DB::rollback();
            if($code==null){
                $code = 500;
            }
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
            
        }
        return $this->returnJSONMessage($code);
	}

    public function reUploadFiles($process_ID,Request $request){
        try{
            \DB::beginTransaction();

            $pup=ProcessRegisterCustomerPayment::findProcess($process_ID);
            if($pup==null){
                $code = 404;
                throw new \Exception("Proceso con ese ID no existe", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                $code = 400;
                throw new \Exception("El proceso ya se completo", 1);
            }
            
            $procedure = $pup->procedureEntryRel;

            $description = $request->input('description', '');
            $table_type = 'procedure_entry';
            $old_fid = $request->input('file_id', false);

            if(!isset($request['file'])){
                throw new \Exception("Archivo no es válido", 1);
            }

            $params = array();
            $params['fieldname'] = 'file';
            $params['subfolder'] = 'newPolicy/'.$procedure->id;
            $params['table_type'] = $table_type;
            $params['table_id'] = $procedure->id;
            $params['data'] = json_encode(array('process_id' => $process_ID));
            $params['multiple'] = false;

            if( $old_fid ){
                //if get file id try to update
                $updated = $this->updateFile($request, $old_fid, $params);
                $uploadedFiles = array($updated);
            }
            else{
                $uploadedFiles = $this->uploadFiles($request, $params);
            }

            \DB::commit();
            $code = 200;
            $this->novaMessage->setData(["file_id"=>$uploadedFiles[0]]);
        }catch( \Exception $e ){
            $code = 500;
            \DB::rollback();
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }
        return $this->returnJSONMessage($code);
    }//end reUploadFiles

    public function deteleFileProcess(Request $request, $process_ID){
        try{
            \DB::beginTransaction();

            $pup=ProcessRegisterCustomerPayment::findProcess($process_ID);
            if($pup==null){
                $code = 404;
                throw new \Exception("Proceso con ese ID no existe", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                $code = 400;
                throw new \Exception("El proceso ya se completo", 1);
            }
            
            $procedure = $pup->procedureEntryRel;

            $input = $request->all();

            if(!isset($input["file_id"])){
                throw new \Exception("Petición es inválida");
            }

            $fid = $input["file_id"];
            $fe = FileEntry::find($fid);
            if(!($fe->table_type=="procedure_entry" &&
                $fe->table_id==$procedure->id)){
                throw new \Exception("Archivo es inválido");
            }

            $this->deleteFile($fe->id);
             \DB::commit();
             $code = 200;
            $this->novaMessage->setData(["id"=>$fe->id]);
        }catch( \Exception $e ){
            $code = 500;
            \DB::rollback();
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }
        return $this->returnJSONMessage($code);
    }//end deleteFile
}