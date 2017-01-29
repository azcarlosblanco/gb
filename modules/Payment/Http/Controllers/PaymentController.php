<?php namespace Modules\Payment\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Policy\Entities\Policy;
use Modules\Payment\Entities\PolicyCost;
use Modules\Emission\Entities\ProcessRegisterCustomerPayment;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Payment\Entities\BankAccountType;
use Modules\Payment\Entities\CreditCardType;
use Modules\Payment\Entities\CreditCardBrand;
use Modules\Payment\Entities\CreditCardWayPay;
use App\FileEntry;
use App\ProcessEntry;
use App\ProcessCatalog;
use Modules\Reception\Entities\RequestPolicyData;
use Modules\Plan\Entities\NumberPayments;


class PaymentController extends NovaController {
	
	public function index(Request $request)
	{
		$code=200;
		try{
			$result= PolicyCost::with('policy')
			                   ->get();
		    
			$state=PolicyCost::stateQuoteArray();
          
            if($request->has('withView') ) {
				$data=array();
				$index=0;
				foreach ($result as $key => $value) {
					
					$data[$index]['policy_number']    = $value['policy_id'];
					$data[$index]['name']             = $value->policy->customer->getFullNameAttribute();;
					$data[$index]['quote_number']     = $value['quote_number'];
					$data[$index]['total']            = $value['total'];
					$data[$index]['state']            = $state[$value["state"]];	
					if($value['state']==0){   //Valida si la poliza esta pagada para mostrar boton ver
                        $data[$index]['buttons'] = array(
	                                               	array(
                                 							'class' => 'available',
                                 							'active' => true,
                                 							'link'  => '.view',
                                 							'params' => [
                                 									   'process_ID'   => $value->id,
                                 										],
                                 							'icon' => 'glyphicon glyphicon-eye-open',
                                 							'description' => 'Ver detalle de Pago'
                            							),
                            						
	                                           );
                       }
                    else{
                    	$data[$index]['buttons'] = array(
                        	                        array(
                                 							'class' => 'available',
                                 							'active' => true,
                                 							'link'  => '.pay_policy',
                                 							'params' => [
                                 							'id'   => $value->id,
                                 										],
                                 							'icon' => 'glyphicon glyphicon-file',
                                 							'description' => 'Registrar Pago Poliza'
                            							),
                        	                        array(
                                 							'class' => 'available',
                                 							'active' => true,
                                 							'link'  => '.confirm_pay_policy',
                                 							'params' => [
                                 							'id'   => $value->id,
                                 										],
                                 							'icon' => 'glyphicon glyphicon-file',
                                 							'description' => 'Confirmar Pago Poliza'
                            							),
	                                               	                            						
	                                           );
                    }				
					
					$index++;
				}
				$this->novaMessage->setData($this->renderIndex($data));
			}else{
				$this->novaMessage->setdata($result);
			}
		}catch(\Exception $e){
			//show message error
			$code=500;
    		$this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
		}		
		return $this->returnJSONMessage($code);
		
	}

	public function form($id,Request $request){
        try{

                     //policy data
        	$policy=Policy::with('customer')
        	              ->find($id);

             throw new \Exception($id);
            
            if($policy==null){
        		throw new \Exception("La poliza solicitada no existe");
        	}
            $catalog['payment_method'] = PaymentMethod::pluck('display','id');
            $catalog['payment_name'] = PaymentMethod::pluck('method','id');
            $catalog['account_type'] = BankAccountType::pluck('display_name','id');
            $catalog['credit_card_type'] = CreditCardType::pluck('display_name','id');
            $catalog['credit_card_brand'] = CreditCardBrand::pluck('display_name','id');
            $catalog['form_pay'] = CreditCardWayPay::pluck('display_name','id');
            $catalog['number_payments'] = NumberPayments::find($policy->payments_number_id)->description;
            $catalog['policy_number'] = $policy->policy_number;
            $catalog['client_name'] = $policy->customer->full_name;
            
            //cost policy
            $costs = $policy->getPolicyCosts();
            $data['id'] = $id;
            $data['catalog'] = $catalog;
            $data['catalog']['costs'] = $costs;
            
            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}


	 private function renderIndex($content){
        $index['display']['title']='Pago Poliza';
        $index['display']['header']=array(
        	                array('label' =>'# Poliza',
                                  'filterType'=>'text',
                                  'fieldName' =>'policy_number'),
                            array('label' =>'Titular',
                                  'filterType'=>'text',
                                  'fieldName' =>'name'),
                             array('label' =>'Cuota',
                            	  'filterType'=>'text',
                                  'fieldName' =>'quote_number'),
                             array('label' =>'Total',
                            	  'filterType'=>'text',
                                  'fieldName' =>'total'),
                             array('label' =>'Estado',
                            	  'filterType'=>'text',
                                  'fieldName' =>'state'),
                             array('label' =>'Acciones',
                            	  'filterType'=>'text',
                                  'fieldName' =>'buttons')
                        );
        $index['list']=$content;
       
        //}
        return $index;
    }
	
}