<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use App\ProcedureEntry;
use App\ProcedureCatalog;
use App\ProcessEntry;
use App\ProcessCatalog;
use Modules\Policy\Entities\Policy;
use Modules\Agente\Entities\Agente;
use Modules\Customer\Entities\Customer;
use App\Http\Controllers\Nova\NovaController;
use Illuminate\Http\Request;
use Modules\Reception\Entities\RequestPolicyData;
use JWTAuth;
use Modules\Authorization\Entities\Role;

class ReporteTramitesController extends NovaController{
	
	public function index()
	{
		return view('emission::index');
	}
	
	public function tramitesActuales(Request $request){
		$listProcedures=
			ProcedureEntry::getListPendingProcedure(
								'newpolicy');
		$index=0;
		$result=[];
		$prs_catalog=ProcessCatalog::get()
									->keyBy('id');
		foreach ($listProcedures as $procedure_process) {

			//Get information policy and client
			if(is_null($procedure_process->policy_id)){
				$data=$this->getRequestPolicyData($procedure_process->id);
				$result[$index]['policy_number']="";
				$result[$index]['client']=$data['customer_fullname'];
				$agente=Agente::find($data['agente_id']);
				$result[$index]['agent']=$agente->getFullNameAttribute();
			}else{
				$policy=Policy::find($procedure_process->policy_id);
				$result[$index]['policy_number']=$policy->policy_number;
				$result[$index]['client']=$policy->customer->getFullNameAttribute();
				$agente=Agente::find($policy->agente_id);
				$result[$index]['agent']=$agente->getFullNameAttribute();
			}
			$result[$index]['procedure_id']=$procedure_process->id;
			$result[$index]['operator']=$procedure_process->u_name." ".$procedure_process->u_lastname;
			$result[$index]['pcd_start_date']=date('d-m-Y',strtotime($procedure_process->pcd_start_date));
			$result[$index]['prs_start_date']=date('d-m-Y',strtotime($procedure_process->prs_start_date));
			$result[$index]['state']=$prs_catalog[$procedure_process->process_catalog_id]->description;

			/*$first  = new \DateTime($procedure_process->prs_start_date);
			$second = new \DateTime($procedure_process->pcd_start_date);
			$diff = $first->diff($second);
			$result[$index]['pcd_lapsed_time']=$diff->format( '%H:%I:%S' );*/
			$result[$index]['searchingField']=strtoupper(
												$result[$index]['policy_number'].
												$result[$index]['client'].
												$result[$index]['procedure_id'].
												$result[$index]['state'].
												$result[$index]['operator']
											);
			$index++;
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
			$this->novaMessage->setData($this->formTramitesActuales($list));
		}else{
			$this->novaMessage->setData($list);
		}
		return $this->returnJSONMessage();
	}

	public static function getRequestPolicyData($idProcedure){
		$prc = ProcessCatalog::where('name', 'InitialDocumentation')->value('id');
		$ip = ProcessEntry::where('procedure_entry_id',$idProcedure)
								->where('process_catalog_id',$prc)
								->first();

		$init_data=RequestPolicyData::where('process_id',$ip->id)->first();
		$data['customer_fullname']=$init_data->customer_fullname;
		$data['agente_id']=$init_data->agente_id;
		return $data;
	}

	public function historialTramites(Request $request){
		$content=[];
		$user = JWTAuth::parseToken()->authenticate();
		$roles_user=$user->roles->lists('name');
		$manager=false;
		foreach ($roles_user as $value) {
			if(in_array($value,['administracion','emision_manager','recepcion_manager'])){
				$manager=true;
			}
		}

		$userID=($manager)?"":$user->id;

		$procedureCatalog = ProcedureCatalog::where('name','newpolicy')
			  									->first();

		$listProcedures=
			ProcedureEntry::getListFinishedProcedures(
								'newpolicy');
		$index=0;
		$result=[];
		$prs_catalog=ProcessCatalog::orderBy('seq_number')
										->where('procedure_catalog_id',$procedureCatalog->id)
										->get();
		$list=array();
		foreach ($listProcedures as $pp) {
			$idProcedure=$pp->id;
			$list[$idProcedure]['procedure_id']=$idProcedure;
			//elapsed time by process
			$first  = new \DateTime($pp->prs_start_date);
			$second = new \DateTime($pp->prs_end_date);
			$diff = $first->diff($second);
			$list[$idProcedure][$pp->process_catalog_id]=
						$diff->format( '%H:%I:%S' );
			//elapsed time by procedure
			$first  = new \DateTime($pp->pcd_start_date);
			$second = new \DateTime($pp->pcd_end_date);
			$diff = $first->diff($second);
			if(!isset($list[$idProcedure]['total_time'])){
				$list[$idProcedure]['total_time']=$diff->format( '%H:%I:%S' );
			}
		}

		$result=array();
		foreach ($list as $values) {
			$result[]=$values;
		}

		if($request->has('withView')){
			$this->novaMessage->setData(
				$this->formHistorialTramites(
								$result,
								$prs_catalog));
		}else{
			$this->novaMessage->setData($list);
		}
		return $this->returnJSONMessage();
	}

	public function historialTramitesByOperator(Request $request){
		$content=[];
		$user = JWTAuth::parseToken()->authenticate();
		$roles_user=$user->roles->lists('name');
		$manager=false;
		foreach ($roles_user as $value) {
			if(in_array($value,['administracion','emision_manager','recepcion_manager'])){
				$manager=true;
			}
		}

		$userID=($manager)?"":$user->id;
		$listProcedures=
			ProcedureEntry::getListFinishedProcedures(
								'newpolicy');
		$index=0;
		$result=[];
		$prs_catalog=ProcessCatalog::orderBy('seq_number')
									->get();
		$list=array();
		$time=0;
		foreach ($listProcedures as $pp) {
			$userID=$pp->u_id;
			$list[$userID]['operator']=$pp->u_name." ".$pp->u_lastname;
			//elapsed time by process
			$first  = new \DateTime($pp->prs_start_date);
			$second = new \DateTime($pp->prs_end_date);
			$diff = $first->diff($second);
			$list[$userID][$pp->process_catalog_id]=
						$diff->format( '%H:%I:%S' );
			if(isset($list[$userID]['total_time'])){
				$list[$userID]['total_time']+=$this->convertSeconds($diff);
			}else{
				$list[$userID]['total_time']=$this->convertSeconds($diff);
			}
		}

		$result=array();
		foreach ($list as $values) {
			$result[]=$values;
		}

		if($request->has('withView')){
			$this->novaMessage->setData(
				$this->formHistorialTramitesByOperator(
								$result,
								$prs_catalog));
		}else{
			$this->novaMessage->setData($list);
		}
		return $this->returnJSONMessage();
	}

	private function convertSeconds($interval_obj){
		$time_seconds = $interval_obj->h * 3600 + 
						$interval_obj->m * 60 + 
						$interval_obj->s;
		return $time_seconds;
	}
	

	public function averageTimeByProcedure(Request $request){
		$listProcedures=
			ProcedureEntry::averageTimeByProcess(
								'newpolicy');
		$result=array();
		foreach ($listProcedures as $procedure) {
			$result[]=[$procedure->time_diff,$procedure->process_catalog_id];
		}
		return $listProcedures;
	}

	public function averageTimeByUser($userID,Request $request){
		$listProcedures=
			ProcedureEntry::getListFinishedProcedures(
								'newpolicy',
								$userID);
		return $listProcedures;
	}

	public function formTramitesActuales($content){
		$index['display']['singular']='Emisión';
		$index['display']['plural']='Emisiones';
		$index['display']['title']='Emisión - Listado Trámites Actuales';
		$index['display']['header']=array(
                        array('label' =>'Trámite',
                              'filterType'=>'text',
                              'fieldName' =>'procedure_id'),
                        array('label' =>'Operador',
                        	  'filterType'=>'text',
                              'fieldName' =>'operator'),
                        array('label' =>'Agente',
                        	  'filterType'=>'text',
                              'fieldName' =>'agent'),
                        array('label' =>'Póliza',
                              'filterType'=>'text',
                              'fieldName' =>'policy_number'),
                        array('label' =>'Cliente',
                        	  'filterType'=>'text',
                              'fieldName' =>'client'),
                        array('label' =>'Fecha Inicio Trámite',
                        	  'filterType'=>'date',
                              'fieldName' =>'pcd_start_date'),
                        array('label' =>'Fecha Inicio Proceso',
                        	  'filterType'=>'date',
                              'fieldName' =>'prs_start_date'),
                        /*array('label' =>'Tiempo Transcurrido',
                        	  'filterType'=>'date',
                              'fieldName' =>'pcd_lapsed_time'),*/
                        array('label' =>'Estado',
                        	  'filterType'=>'text',
                              'fieldName' =>'state')
                    );
        $index['list']=$content;
        return $index;
	}

	public function formHistorialTramites($content,$listaProcesos){
		$columns=[];
		$columns[0]=array('label' =>'Trámite',
                              'filterType'=>'text',
                              'fieldName' =>'procedure_id');
		$index=1;
		foreach($listaProcesos as $proceso) {
        	$columns[$index]=array('label' => $proceso->description,
							        	  'filterType'=> 'text',
							              'fieldName' => $proceso->id."");
        	$index++;
        }
		$form['display']['singular']='Emisión';
		$form['display']['plural']='Emisiones';
		$columns[count($listaProcesos)+1]=
							array('label' =>'Tiempo Total',
                              'filterType'=>'text',
                              'fieldName' =>'total_time');
		$headers=array();
		foreach ($columns as $values) {
			$headers[]=$values;
		}
		$form['display']['header']=$headers;
        $form['list']=$content;
        $form['display']['title']='Emisión - Historial Trámites';
        return $form;
	}

	public function formHistorialTramitesByOperator($content,$listaProcesos){
		$columns=[];
		$columns[0]=array('label' =>'Operador',
                              'filterType'=>'text',
                              'fieldName' =>'operator');
		$index=1;
		foreach($listaProcesos as $proceso) {
        	$columns[$index]=array('label' => $proceso->description,
							        	  'filterType'=> 'text',
							              'fieldName' => $proceso->id);
        	$index++;
        }
		$form['display']['singular']='Emisión';
		$form['display']['plural']='Emisiones';
		
		$columns[count($listaProcesos)+1]=
							array('label' =>'Tiempo Total',
                              'filterType'=>'text',
                              'fieldName' =>'total_time');
		$headers=array();
		foreach ($columns as $values) {
			$headers[]=$values;
		}
		$form['display']['header']=$headers;
        $form['list']=$content;
        return $form;
	}
}