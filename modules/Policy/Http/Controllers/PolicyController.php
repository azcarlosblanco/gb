<?php namespace Modules\Policy\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use Modules\Affiliate\Entities\Affiliate;
use Modules\Affiliate\Entities\AffiliatePolicy;
use App\Http\Controllers\Nova\NovaController;
use Modules\Policy\Entities\Policy;
use Modules\Policy\Entities\PolicyCalculator;
use Modules\Plan\Entities\Plan;

class PolicyController extends NovaController {

	public function index(Request $request){
		$code=null;
		try{
	        $pagenum = $request->input('pagenum', 0);
	        $policies = Policy::with('customer')
	        						->with('deducibles')
	        						->with('planDeducible')
	        						->with('agente');

	        if( $pagenum > 0 ){
	            $policies = $policies->orderBy("created_at",'desc')
	            						->paginate($pagenum);
	        }else{
	            $policies = $policies->orderBy("created_at",'desc')
	            						->get();
	        }

            $list=array();
            $index=0;
            $plan = Plan::pluck('name','id');

            $stateList = Policy::getListStatesPolicyDesc();
            foreach ($policies as $value) {
            	$list[$index]['policy_number'] = $value['policy_number'];
            	$deducible = $value->planDeducible;
            	$list[$index]['state'] = isset($stateList[$value['state']])?
            									$stateList[$value['state']]:
            									$value['state'];
            	$list[$index]['plan_deducible'] = $deducible->plan->name." / ".$deducible->name;
                $list[$index]['start_date'] = date("m/d/Y",strtotime($value->start_date));
                $list[$index]['end_date'] = date("m/d/Y",strtotime($value->end_date));
                $list[$index]['agent'] = $value->agente->getFullNameAttribute();
            	$list[$index]['customer_name'] = $value->customer->getFullNameAttribute();
                $list[$index]['buttons'] = array(
                           	array(
                                 'class' => 'available',
                                 'active' => true,
                                 'link'  => '.view',
                                 'params' => [
                                 		'process_ID'   => $value->id,
                                 			],
                                 'icon' => 'glyphicon glyphicon-eye-open',
                                 'description' => 'Ver'
                            ),
                            array(
                                 'class' => 'available',
                                 'active' => true,
                                 'link'  => '.list_files',
                                 'params' => [
                                 		'id'   => $value->id,
                                 			],
                                 'icon' => 'glyphicon glyphicon-file',
                                 'description' => 'Archivos'
                            ),
                       );
                $index++;
            }

	        if($request->has('withView') && $request['withView']){
                $this->novaMessage->setData(
                                        $this->renderIndex($list));
	        }else{
	            $this->novaMessage->setData($plans);
	        }
	        $code=200;
	   	}catch(\Exception $e){
	    	if($code==null){
	    		$code=500;
	    	}
	    	$this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
	    }
	    return $this->returnJSONMessage($code);
	}

	private function renderIndex($content){
		$index['display']['title']="Pólizas";
		$index['display']['header'][]=array('label' =>'# Póliza',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'policy_number');
		$index['display']['header'][]=array('label' =>'Titular',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'customer_name');
		$index['display']['header'][]=array('label' =>'Plan / Deducible',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'plan_deducible');
		$index['display']['header'][]=array('label' =>'Agente',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'agent');
		$index['display']['header'][]=array('label' =>'Inicio Cobertura',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'start_date');
		$index['display']['header'][]=array('label' =>'Fin Cobertura',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'end_date');
		$index['display']['header'][]=array('label' =>'Estado',
		                                    'filterType'=>'text',
		                                    'fieldName' =>'state');
        $index['display']['header'][]= array('label' =>'Botenes de Acción',
                                  			   'fieldName' =>'buttons');
        $index['list']=$content;
        return $index;
	}

	public function policyListFiles($id,Request $Request){
		$code = null;
		try{
			$policy = Policy::find($id);
			if($policy==null){
				$code = 400;
				throw new \Exception("La póliza no existe");
			}
			
			$proEntrys = \App\ProcedureEntry::where("policy_id",$policy->id)
												->pluck("id");

			$files = \App\FileEntry::where("table_type","procedure_entry")
										->whereIn("table_id",$proEntrys)
										->select("id","original_filename","description","data")
										->get();

			$docsIdDescList = \App\ProcedureDocument::pluck("description","id");
			$docsNameDescList = \App\ProcedureDocument::pluck("name","id");
			$otherListDocs = $this->otherDocuemntsName();

			$listFile = array();
			foreach ($files as $key => $file) {
				$listFile[$key]=$file;
				if(isset($docsNameDescList[$file["description"]])){
					$listFile[$key]["description"] = $docsNameDescList[$file["description"]];
				}
				$dataf = (array)(json_decode($file["data"]));
				if(isset($dataf["procedure_document_id"])){
					$listFile[$key]["procedure_document_id"] = $dataf["procedure_document_id"];
																
					$listFile[$key]["procedure_document_desc"] = $docsIdDescList[
																$dataf["procedure_document_id"]
																	]; 
				}else{
					$listFile[$key]["procedure_document_desc"] = 
										isset($otherListDocs[$file["description"]])?
										$otherListDocs[$file["description"]]:
										$file["description"];
					$listFile[$key]["description"] = 
										isset($otherListDocs[$file["description"]])?
										$otherListDocs[$file["description"]]:
										$file["description"];
				}
			}
			$code = 200;
			$data['files'] = $listFile;
			$this->novaMessage->setData($data);
		}catch(Exception $e){
			if($code==null){
	    		$code=500;
	    	}
	    	$this->novaMessage->addErrorMessage('ERROR',$e->getMessage());
		}
		return $this->returnJSONMessage($code);
	}

	private function otherDocuemntsName(){
		$list['payment_proof_bd']='Prueba de pago póliza';
		$list['invoice_client']='Factura Cliente';
		$list['signed_policy']='Póliza fimrada cliente';
		$list['guia_remision_to_db']='Guía remisión póliza firmadao BD';
		$list['receipt_bd']='Papeleta Recibido Póliza Firmada BD';
		return $list;
	}

	public function update(){}

	public function calculatePremiums($id){
		try{
			//\DB::beginTransaction();

			$policy = Policy::findOrFail($id);
			$calculator = new PolicyCalculator($policy);
			$resp = $calculator->calculatePremiums();

			//\DB::commit();
			$this->novaMessage->setData(array('data'=>$resp));
  		return $this->returnJSONMessage(200);
		}catch( \Exception $e ){
			//\DB::rollback();
			//show message error
  		$this->novaMessage
              ->addErrorMessage('ERROR',$e->getMessage());
  		return $this->returnJSONMessage(404);
		}
	}//end calculatePremiums

    public function view($id, Request $request)
	{
		try{
            
            $policy = Policy::with('planDeducible')
        						->with('agente')
        						->with('affiliates')
        						->find($id);

        	if($policy==null){
        		throw new \Exception("La poliza solicitada no existe");
        	}
            
			$catalog=array();
			$catalog['catalog']=array();         
            $data['catalog'] = $catalog;

            $data['policy'] = $policy->toArray();
            $data['policy']['plan'] = $policy->planDeducible->plan->name;
			$data['policy']['deductibles'] = $policy->planDeducible->name;
			$data['policy']['start_date'] = date("m/d/Y",strtotime($policy->start_date));
			$data['policy']['end_date'] = date("m/d/Y",strtotime($policy->end_date));
			$data['policy']['agente_name'] = $policy->agente->full_name;
			$data['policy']['state'] = $policy->getStatePolicyDesc();
			$data['policy']['affiliates_obj'] = array();
			
			$affiliatesPolicy = $policy->affiliates;
			foreach ($affiliatesPolicy as $afpolicy) {
			    if(is_null($afpolicy->dismiss_date)){
					$affiliate = $afpolicy->affiliate;
					$afid = $affiliate->id;
					if($afpolicy->affRole->name=="titular"){
						$data['policy']['affiliates_obj'][$afid]['pid_num'] = $affiliate->pid_num;
					}else{
						$data['policy']['affiliates_obj'][$afid]['pid_num'] = "";
					}
					$data['policy']['affiliates_obj'][$afid]['name'] = $affiliate->full_name;
					$data['policy']['affiliates_obj'][$afid]['dob'] = $affiliate->dob;
					$data['policy']['affiliates_obj'][$afid]['role'] = $afpolicy->affRole->name;
					$data['policy']['affiliates_obj'][$afid]['edate'] = 
					                           date("m/d/Y",strtotime($afpolicy->effective_date));
			    }
			}

            $this->novaMessage->setData($data);
            return $this->returnJSONMessage();
            
        }catch(ModelNotFoundException $ex){
        	$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
        }
        return $this->returnJSONMessage();
	}
}
