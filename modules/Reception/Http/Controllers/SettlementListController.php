<?php namespace Modules\Reception\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use App\ProcedureCatalog;
use App\ProcedureEntry;
use App\ProcessCatalog;
use App\ProcessEntry;
use Modules\Policy\Entities\Policy;
use Modules\Customer\Entities\Customer;
use Modules\Agente\Entities\Agente;
use Illuminate\Support\Facades\Auth;
use Modules\Reception\Entities\RequestPolicyData;
use JWTAuth;
use Modules\Authorization\Entities\Role;

class ReceptionController extends NovaController {
	
	protected $module_path='reception';

	function __construct()
	{
        parent::__construct();
	}

	public function pendingProcedures(Request $request)
	{
		$content=[];
		$user = JWTAuth::parseToken()->authenticate();
		$roles_user=$user->roles->lists('name');
		$manager=false;
		foreach ($roles_user as $value) {
			if(in_array($value,['administracion',
								'emision_manager',
								'recepcion_manager',
								'settlement_manager'])){
				$manager=true;
			}
		}
		//$manager=true;
		$userID=($manager)?"":$user->id;

		$procedure_name='newpolicy';
		$type="Emisiones";
		if($request['type']=='claim'){
			$procedure_name='claims';
			$type="Reclamos";
		}else if($request['type']=='settlement'){
			$procedure_name='settlement';
			$type="Liquidaciones";
		}

		$listProcedures=
			ProcedureEntry::getListPendingProcedure(
							$procedure_name,
							$userID);

		$actionButtons=[];
		$result=[];
		$affliates=array();
		foreach ($listProcedures as $item) {
			if(isset($result[$item->id]))
				continue;

			$procedure=ProcedureEntry::find($item->id);
			if(empty($actionButtons)){
				$role=Role::where('name','recepcion')->first();
				$actionButtons=$procedure->getListActionButtons($role->id);
			}
			$buttons=$procedure
						->getListButtons($actionButtons,
										$item->current_process_id,
										$item->process_catalog_id);
			if(isset($buttons['current_description'])){
				//Get information policy and client
				if(is_null($procedure->policy_id)){
					$data=self::getRequestPolicyData($item);
					$result[$item->id]['policy_number']="";
					$result[$item->id]['client']=$data['customer_fullname'];
					$agente=Agente::find($data['agente_id']);
					$result[$item->id]['agent']=$agente->getFullNameAttribute();
				}else{
					$result[$item->id]['policy_number']=$procedure->policy->id;
					if($procedure_name=='settlement'){
						//obtener el nombre del afiliado
						$affiliate=$procedure->claims()
									->with('affiliatePolicy')
									->first()
									->affiliatePolicy
									->affiliate;
						$result[$item->id]['affiliate']=$affiliate->getFullNameAttribute();
					}
					$result[$item->id]['client']=$procedure
													->policy
													->customer
													->getFullNameAttribute();
					
					
					$agente=Agente::find($procedure->policy->agente_id);
					$result[$item->id]['agent']=$agente->getFullNameAttribute();
				}
				$result[$item->id]['procedure_id']=$procedure->id;
				$result[$item->id]['state']=$buttons['current_description'];
				$result[$item->id]['pcd_start_date']=date('d-m-Y',strtotime($item->pcd_start_date));
				$result[$item->id]['prs_start_date']=date('d-m-Y',strtotime($item->prs_start_date));
				$result[$item->id]['buttons']=$buttons['buttons'];
				$result[$item->id]['operator']=$item->u_name." ".$item->u_lastname;
				$result[$item->id]['searchingField']=strtoupper(
									$result[$item->id]['policy_number'].
									$result[$item->id]['client'].
									$result[$item->id]['procedure_id'].
									$result[$item->id]['state'].
									$result[$item->id]['operator']
											);
			}
		}
		//search 
		$search="";
		if($request->has('search_data')){
			$search=strtoupper($request['search_data']);
		}
		$list=array();
		foreach ($result as $values) {
			if ($search==="" || strpos($values['searchingField'], $search) !== false) 
				$list[]=$values;
		}
		
		if($request->has('withView')){
			$this->novaMessage->setData($this->renderIndex($list,$type));
		}else{
			$this->novaMessage->setData($list);
		}
		return $this->returnJSONMessage();
	}

	public static function getRequestPolicyData($item){
		$prc = ProcessCatalog::where('name', 'InitialDocumentation')
									->value('id');
		$ip = ProcessEntry::where('procedure_entry_id',$item->id)
								->where('process_catalog_id',$prc)
								->first();

		$init_data=RequestPolicyData::where('process_id',$ip->id)->first();
		$data['customer_fullname']=$init_data->customer_fullname;
		$data['agente_id']=$init_data->agente_id;
		return $data;
	}

	private function renderIndex($content,$type){
		$index['display']['title']="Recepción - $type Pendientes";
		$index['display']['header'][]=array('label' =>'Trámite',
		                                  'filterType'=>'text',
		                                  'fieldName' =>'procedure_id');
		$index['display']['header'][]=array('label' =>'Póliza',
		                                  'filterType'=>'text',
		                                  'fieldName' =>'policy_number');
		if($type=='Liquidaciones'){
			//afiliado
			$index['display']['header'][]=array('label' =>'Afiliado',
			                            	  'filterType'=>'text',
			                                  'fieldName' =>'affiliate');
		}else{
			//cliente
			$index['display']['header'][]=array('label' =>'Cliente',
			                            	  'filterType'=>'text',
			                                  'fieldName' =>'client');
		}		
		$index['display']['header'][]=array('label' =>'Inicio Trámite',
				                        	  'filterType'=>'date',
				                              'fieldName' =>'pcd_start_date');
		$index['display']['header'][]=array('label' =>'Inicio Proceso',
				                        	  'filterType'=>'date',
				                              'fieldName' =>'prs_start_date');
        $index['display']['header'][]= array('label' =>'Estado',
			                            	  'filterType'=>'text',
			                                  'fieldName' =>'state');
        $index['display']['header'][]= array('label' =>'Botenes de Acción',
                                  			   'fieldName' =>'buttons');
        $index['list']=$content;
        return $index;
	}
	
}