<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use App\ProcedureCatalog;
use App\ProcedureEntry;
use App\ProcessCatalog;
use App\ProcessEntry;
use Modules\Authorization\Entities\Role;
use Illuminate\Support\Facades\Auth;
use Modules\Agente\Entities\Agente;
use Modules\Reception\Entities\RequestPolicyData;
use JWTAuth;

class EmissionController extends NovaController {
	
	protected $module_path='emission';

	function __construct()
	{
        parent::__construct();
	}

	/*
	 * Return the procedures that are pending and has been asigned to
	 * the user that is logged
	 */
	public function pendingProcedures(Request $request)
	{

		$content=[];
		$user = JWTAuth::parseToken()->authenticate();
		$roles_user=$user->roles->lists('name');
		$manager=false;
		foreach ($roles_user as $value) {
			if(in_array($value,['administracion','emision_manager','recepcion_manager'])){
				$manager=true;
			}
		}

		//$manager=true;
		
		$userID = ($manager)?"":$user->id;

		$listProcedures=
			ProcedureEntry::getListPendingProcedure(
							'newpolicy',
							$userID);
			
		$procedure_name = 'newpolicy';

		$actionButtons=[];
		$index=0;
		$result=[];
		foreach ($listProcedures as $item) {
			$procedure=ProcedureEntry::find($item->id);
			if(empty($actionButtons)){
				$role=Role::where('name','emision')->first();
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
					$result[$item->id]['policy_number']=$procedure->policy->policy_number;
					$result[$item->id]['client']=$procedure->policy->customer->getFullNameAttribute();
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

				//add button to cancel the procedure
				$result[$item->id]['buttons'][] = array(
													 'class' => 'danger',
	                                                 'active' => true,
	                                                 'link'  => '.cancel-procedure',
												     'icon' => 'glyphicon glyphicon-trash',
                                                 	 'description' => 'Cancelar Procedimiento',
												     'params' => [
												         'id' => $item->id,
												         'message' => 'Confirma que desea dar de baja el trámite. Esta acción no puede ser revertida',
												         'method' => 'DELETE',
												         'uri' => 'emission/pending/cancelProcedure',
												     ]
												);
				$index++;
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
			$this->novaMessage->setData($this->renderIndex($list));
		}else{
			$this->novaMessage->setData($list);
		}
		return $this->returnJSONMessage();
	}

	public static function getRequestPolicyData($item){
		$prc = ProcessCatalog::where('name', 'InitialDocumentation')->value('id');
		$ip = ProcessEntry::where('procedure_entry_id',$item->id)
								->where('process_catalog_id',$prc)
								->first();

		$init_data=RequestPolicyData::where('process_id',$ip->id)->first();
		$data['customer_fullname']=$init_data->customer_fullname;
		$data['agente_id']=$init_data->agente_id;
		return $data;
	}

	public function cancelProcedure($id, Request $req){
		try{
			\DB::beginTransaction();
			$pr = ProcedureEntry::find($id);
			if(!$pr->isActive()){
				throw new \Exception("El trámite no está activo, por lo tanto no puede ser eliminado",422);
			}

			//why the procedure is been deleted
			$reason = $req->input('reason',"");
			$user = JWTAuth::parseToken()->authenticate();

			$pr->cancel($reason,$user->id);
			\DB::commit();
			$code = 200;
		}catch(\Exception $e){
			\DB::rollback();
			$code = 500;
			$this->novaMessage->addErrorMessage('Error', $e->getMessage());
		}	
		return $this->returnJSONMessage($code);
	}

	/*
	 * This function return all the procedures that have been process by the 
	 * logged user
	 */
	public function listProcedures(){

	}

	private function renderIndex($content){
		$index['display']['title']='Emisión - Listado Trámites Pendientes';
		$index['display']['header']=array(
                        array('label' =>'#',
                              'filterType'=>'text',
                              'fieldName' =>'procedure_id'),
                        array('label' =>'# Póliza',
                              'filterType'=>'text',
                              'fieldName' =>'policy_number'),
                        array('label' =>'Cliente',
                        	  'filterType'=>'text',
                              'fieldName' =>'client'),
                        array('label' =>'Agente',
                        	  'filterType'=>'text',
                              'fieldName' =>'agent'),
                        array('label' =>'Inicio Trámite',
                        	  'filterType'=>'date',
                              'fieldName' =>'pcd_start_date'),
                        array('label' =>'Inicio Proceso',
                        	  'filterType'=>'date',
                              'fieldName' =>'prs_start_date'),
                        array('label' =>'Acciones',
                              'fieldName' =>'buttons')
                    );
        $index['list']=$content;
        return $index;
	}
	
}