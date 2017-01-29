<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use App\Http\Controllers\Nova\NovaController;
use Modules\Emission\Entities\ProcessRegisterInvoice;
use Modules\Payment\Entities\PaymentMethod;
use App\FileEntry;
use Illuminate\Http\Request;
use Modules\Policy\Entities\Policy;
use Modules\Payment\Entities\PolicyPaymentDetail;
use App\UploadAndDownloadFile;
use Modules\Payment\Entities\PolicyCost;

class  RegisterInvoiceController extends NovaController {

    use UploadAndDownloadFile;
	
	function __construct()
	{
        parent::__construct();
	}

	public function form($process_ID){
        try{
            $pup=ProcessRegisterInvoice::findProcess($process_ID);
            
            $policy = $pup->procedureEntryRel->policy;
            
            $catalog['payment_method'] = PaymentMethod::pluck('display','method');
            $catalog['policy_number'] = $policy->policy_number;
            $catalog['client_name'] = $policy->customer->full_name;

            //current qoute
            $pq = $policy->getPolicyQuote(1);
            $catalog['total_quote'] = $pq['total'];

            //current payment
            $payments = $pq->getPaymentMethods();
            $current_payment = array();
            foreach ($payments as $key => $payment_array) {
                foreach ($payment_array as $payment) {
                    if($payment->state == PolicyPaymentDetail::S_WAIT_CONFIRMATION){
                        $current_payment = $payment;
                        $current_payment['payment_method'] = $key;
                    }
                }
            }

            $data['process_ID'] = $process_ID;
            $data['catalog'] = $catalog;
            $data['payment'] = $current_payment;

            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

	public function registerInvoice($process_ID,Request $request){
		\DB::beginTransaction();
        try{
            $pup=ProcessRegisterInvoice::findProcess($process_ID);
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1);
            }

            $input = $request->all();
            $policy = $pup->procedureEntryRel->policy;
            $pq = $policy->getPolicyQuote(1);


            $payments = $pq->getPaymentMethods();
            $current_payment = array();
            foreach ($payments as $payment_array) {
                foreach ($payment_array as $method => $payment) {
                    if($payment->id == $input['id']){
                        $current_payment = $payment;
                    }
                }
            }

            if($current_payment==null){
                throw new \Exception("Petición Inválida");
            }

            //mark the payment as confirm
            $current_payment->state = PolicyPaymentDetail::S_CONFIRM;
            $current_payment->save();

            //if the quote of the payment is complet mark the payment as paid_off
            $pq->state = PolicyCost::S_PAIDOFF;
            $pq->date_paidoff = date("Y-m-d",strtotime($input['payment_date']));
            $pq->save();

            //update policy state to pay


            //upload file that confirm payment
            $table_type = "procedure_entry";
            $table_id   = $pup->procedure_entry_id;
            $params = array();
            $params['fieldname']    = 'confirm_payment_file';
            $params['table_type']   = $table_type;
            $params['table_id']     = $table_id;
            $params['subfolder']    = 'newPolicy/'.$table_id;;
            $params['multiple']     = false;
            $params['description']  = 'payment_proof_bd';
            $uploadedFile = $this->uploadFiles($request, $params);
        
            //upload client invoice
            $params['fieldname']    = 'invoice_file';
            $params['description']  = 'invoice_client';
            $uploadedFile = $this->uploadFiles($request, $params);

            //send the invoice to the client
            $pup->sendInvoiceCustomer($policy,$uploadedFile[0]);

            $pup->finish();

            \DB::commit();
            $this->novaMessage->setRoute(
                    'emission/pending');
            return $this->returnJSONMessage(200);
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}
	
}