<?php namespace Modules\Claim\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use App\ProcedureCatalog;
use App\ProcedureEntry;
use App\ProcessCatalog;
use App\ProcessEntry;
use JWTAuth;
use Modules\Authorization\Entities\Role;
use Modules\Claim\Entities\ClaimSettlement;
use Modules\Claim\Entities\ClaimFile;
use Modules\Affiliate\Entities\Affiliate;
use App\Diagnosis;
use Modules\Policy\Entities\Policy;
use Modules\ClientService\Entities\Ticket;
use Modules\ClientService\Entities\TicketDetail;
use Modules\ClientService\Entities\TicketCat;
use Modules\Customer\Entities\Customer;
use Modules\Agente\Entities\Agente;
use Modules\Claim\Entities\Claim;
use Carbon\Carbon;

class ClaimController extends NovaController {

	protected $module_path='claims';

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
			if(in_array($value,['administracion','claims_manager'])){
				$manager=true;
			}
		}

		$userID=($manager)?"":$user->id;

		$listProcedures=
			ProcedureEntry::getListPendingProcedure(
							'claims',
							$userID);

		$actionButtons=[];
		$index=0;
		$result=[];
		foreach ($listProcedures as $item) {
			$procedure=ProcedureEntry::find($item->id);
			if(empty($actionButtons)){
				$role=Role::where('name','claims')->first();
				$actionButtons=$procedure->getListActionButtons($role->id);
			}
			$buttons=$procedure
						->getListButtons($actionButtons,
										$item->current_process_id,
										$item->process_catalog_id);
			if(isset($buttons['current_description'])){
				//Get information policy and client

				$result[$item->id]['policy_number']=$procedure->policy->policy_number;
				$result[$item->id]['client']=$procedure->policy->customer->getFullNameAttribute();
				$agente=Agente::find($procedure->policy->agente_id);
				$result[$item->id]['agent']=$agente->getFullNameAttribute();

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

	public function timeProcess(Request $request){
		$content=[];

		$user = JWTAuth::parseToken()->authenticate();

		$roles_user=$user->roles->lists('name');
		$manager=false;
		foreach ($roles_user as $value) {
			if(in_array($value,['administracion','claims_manager'])){
				$manager=true;
			}
		}

		//get the list of claims from claim table
		//from claim_procedure get the id of the procedure related with the claim
		//use procedure_entry to get process_entries
		$claims = Claim::with('claimProcedures.procedureEntry.processEntry',
								'affiliatePolicy.affiliate')
								->get();

		$result = array();
		foreach ($claims as $key => $claim) {
			$total = 0;
			$result[$key] = array();
			$result[$key]["num_claim"] = $claim->id;
			$result[$key]["affiliate"] = $claim->affiliatePolicy->affiliate->full_name;
			foreach ($claim->claimProcedures as $cprocedure) {
				foreach ($cprocedure->procedureEntry->processEntry as $process) {
					if($process->state=="finished"){
						$difftimeSec = (strtotime($process->end_date)-strtotime($process->start_date));
					}else{
						$difftimeSec = 0;
					}
					$result[$key]["".$process->process_catalog_id.""]=
													$this->convertSecToTime($difftimeSec)." - ".
													$process->responsible;
					$total = $total + $difftimeSec;
				}
			}
			$result[$key]["total"] = $this->convertSecToTime($total);
		}

		if($request->has('withView')){
			$this->novaMessage->setData($this->renderIndexTimes($result));
		}else{
			$this->novaMessage->setData($result);
		}
		return $this->returnJSONMessage();
	}

	public function claimHistoric(Request $request){
		//obtener la lista de los reclamos por afilicados
		$claims = Claim::with('affiliatePolicy.affiliate')
								->get();


	}



	private function renderIndex($content){
		$index['display']['title']='Reclamos - Trámites Pendientes';
		$index['display']['header']=array(
                        array('label' =>'Trámite',
                              'filterType'=>'text',
                              'fieldName' =>'procedure_id'),
                        array('label' =>'Póliza',
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
                        array('label' =>'Estado',
                        	  'filterType'=>'text',
                              'fieldName' =>'state'),
                        array('label' =>'Acciones',
                              'fieldName' =>'buttons')
                    );
        $index['list']=$content;
        return $index;
	}

	private function renderIndexTimes($content){
		$index['display']['title']='Reclamos - Historial Trámites';
		$index['display']['header']=array(
                        array('label' =>'# Reclamo',
                              'filterType'=>'text',
                              'fieldName' =>'num_claim'),
                        array('label' =>'Afiliados',
                              'filterType'=>'text',
                              'fieldName' =>'affiliate')
                    );

		$procedureCatalog = ProcedureCatalog::where('name','claims')
			  					->orWhere('name','settlement')
			  					->pluck('id');

		/*$pc = ProcessCatalog::with(['procedureCatalog' => function ($query) {
							    $query->where('name', 'claims')
							    	  ->orWhere('name','settlement');
								}])->pluck("description","id");*/


		$pc = ProcessCatalog::whereIn('procedure_catalog_id',$procedureCatalog)
								->pluck("description","id");

		foreach ($pc as $key => $description) {
			$index['display']['header'][] = array(
													'label' => $description,
													'filterType' => 'text',
													'fieldName' => "".$key.""
												 );
		}

		$index['display']['header'][] = array(
													'label' => "Tiempo Total",
													'filterType' => 'text',
													'fieldName' => "total"
												 );

        $index['list']=$content;
        return $index;
	}

	private function renderClaimHistoric($content){
		$index['display']['title']='Historial Reclamos';
		$index['display']['header']=array(
                        array('label' =>'# Reclamo',
                              'filterType'=>'text',
                              'fieldName' =>'claim_num'),
                        array('label' =>'# Póliza',
                              'filterType'=>'text',
                              'fieldName' =>'policy_number'),
                        array('label' =>'Afiliado',
                        	  'filterType'=>'text',
                              'fieldName' =>'affiliate'),
                        array('label' =>'Monto Reclamado',
                        	  'filterType'=>'text',
                              'fieldName' =>'total'),
                        array('label' =>'Monto Cubierto ',
                        	  'filterType'=>'text',
                              'fieldName' =>'total_cover'),
                        array('label' =>'Valor Deducible ',
                        	  'filterType'=>'text',
                              'fieldName' =>'deducible'),
                        array('label' =>'Valor Devuelto ',
                        	  'filterType'=>'text',
                              'fieldName' =>'refund'),
                        array('label' =>'Acciones',
                              'fieldName' =>'buttons')
                    );
        $index['list']=$content;
        return $index;
	}

	public function convertSecToTime($init){
		$hours = floor($init / 3600);
		if($hours<10){
			$hours="0".$hours;
		}
		$minutes = floor(($init / 60) % 60);
		if($minutes<10){
			$minutes="0".$minutes;
		}
		$seconds = $init % 60;
		if($seconds<10){
			$seconds="0".$seconds;
		}
		return "$hours:$minutes:$seconds";
	}

	public function getReport(Request $request){
	  $code=200;
	  	try{
	    	$claim =ClaimSettlement::with('claimfile.claim')
		                           	->get();
		    if($request->has('withView') ) {
			   $data=array();
			   $index=0;

	        	foreach ($claim as $key => $value) {
	        		//only display info about claim that has been finished
	        		//print_r($value);
					$data_settlement = $value->orderBy("updated_at","desc")->first();
					$update = $data_settlement['updated_at'];

					$data_claim = $value['claimfile']['claim']['created_at'];

					$update_date = new Carbon($update);
					$created_date = new Carbon($data_claim);

					$days_pass =$created_date->diffInDays($update_date);

					$data[$index]['policy_id']        = $value['claimfile']['claim']['affiliatePolicy']['policy']
					                                   ['policy_number'];
					$data[$index]['afilliate']        = $value['claimfile']['claim']['affiliatePolicy']
					                                    ['affiliate']['full_name'];
					$data[$index]['diagnosis']        = $value['claimfile']['claim']['diagnosis']['display_name'];
					$data[$index]['claim_id']         = $value['claimfile']['claim_id'];
					$data[$index]['ic_num_claim']     = $value['ic_num_claim'];
					$data[$index]['description']      = $value['claimfile']['description'];
					$data[$index]['amount']           = $value['amount'];
					$data[$index]['days_pass']        = $days_pass;
					$data[$index]['status']            = 'En proceso';
					if($value['claimfile']['claim']['status']==1){
						$data[$index]['status'] = "Terminado";
					}
					$data[$index]['buttons'] = array(
					                                array(
					                                     'class' => 'available',
					                                     'active' => true,
					                                       'link'  => '.view',
					                                       'params' => [
					                                                           'id'   => $value->id,
					                                                         ],
					                                                  'icon' => 'glyphicon glyphicon-eye-open',
					                                                  'description' => 'Ver'
					                                        ),
					                               );
					$index++;
	        	}

            	$this->novaMessage->setData($this->renderReport($data));
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

	public function renderReport($content){
      $index['display']['title']='Reclamos - Reporte';
		$index['display']['header']=array(
                        array('label' =>'#Poliza',
                              'filterType'=>'text',
                              'fieldName' =>'policy_id'),
                        array('label' =>'Afiliado',
                              'filterType'=>'text',
                              'fieldName' =>'afilliate'),
                        array('label' =>'Diagnostico',
                        	  'filterType'=>'text',
                              'fieldName' =>'diagnosis'),
                        array('label' =>'#Reclamo GB',
                        	  'filterType'=>'text',
                              'fieldName' =>'claim_id'),
                        array('label' =>'#Reclamo Best Doctor',
                        	  'filterType'=>'text',
                              'fieldName' =>'ic_num_claim'),
                        array('label' =>'#Factura',
                        	  'filterType'=>'text',
                              'fieldName' =>'description'),
                        array('label' =>'Valor Facturado',
                        	  'filterType'=>'text',
                              'fieldName' =>'amount'),
                        array('label' =>'Dias de Demora',
                        	  'filterType'=>'text',
                              'fieldName' =>'days_pass'),
                        array('label' =>'Estado',
                        	  'filterType'=>'text',
                              'fieldName' =>'status'),
                        array('label' =>'Acciones',
                              'filterType'=>'text',
                              'fieldName' =>'buttons')

                    );
		$index['list']=$content;
        return $index;
	}

	public function getDetailReport($id, Request $request)
	{

     try{
		$claimSet = ClaimSettlement::with('refunds')
		                          ->with('claimfile')
							          ->find($id);

		foreach($claimSet['claimfile']['claim']['claimProcedures'] as $prev){

			$ref = processEntry::where('procedure_entry_id', $prev->procedure_entry_id)
														->where('process_catalog_id','22')
														->first();
			if(count($ref)>0){
					$id = $ref->id;
			}
		}


		if ($claimSet == null){
			  throw new \Exception("La liquidacion del reclamo no existe");
		}

		$data = array();
		$data['detail'] = array();
		$data['detail']['id']                      = $id;
		$data['detail']['name']                   = $claimSet['claimfile']['claim']['affiliatePolicy']
				                                    ['affiliate']['full_name'];
		$data['detail']['factura']                = $claimSet['claimfile']['description'];
		$data['detail']['policy']                 = $claimSet['claimfile']['claim']['affiliatePolicy']['policy']
			                                       ['policy_number'];
		$data['detail']['date']                   = date("m/d/Y H:i",strtotime($claimSet->created_at));
		$data['detail']['uncovered_value']        = $claimSet->uncovered_value;
		$data['detail']['descuento']              = $claimSet->descuento;
		$data['detail']['deducible']              = $claimSet->deducible;
		$data['detail']['coaseguro']              = $claimSet->coaseguro;
		$data['detail']['refunded']               = $claimSet->refunded;
		$data['detail']['expected_deduct']        = $claimSet->expected_deduct;
		$data['detail']['expected_refund']        = $claimSet->expected_refund;
		$data['detail']['refunds'] = array();
		foreach ($claimSet->refunds as $key => $refund) {
			$data['detail']['refunds'][$key]['amount'] = $refund->value;
		  	$data['detail']['refunds'][$key]['payment_method'] = $refund->paymentMethod->display;
		  	$data['detail']['refunds'][$key]['pay_date'] = $refund->pay_date;
		  	$data['detail']['refunds'][$key]['to_supplier'] = $refund->to_supplier;
		}

		$claimID = $claimSet['claimfile']['claim_id'];
		$claimFileID = $claimSet['claimfile']['id'];
		$settlementID = $claimSet['id'];
		$tickets = Ticket::where("table_type","claim_settlement")
								->where("table_id",$settlementID)
								->get();
		foreach ($tickets as $key1 => $ticket) {
			$data['detail']['ticketsdetail'][$key1]['id']=$ticket['id'];
    		$data['detail']['ticketsdetail'][$key1]['description']=$ticket['ticket_cat']['display_name'];
		}
		$tickets = Ticket::where("table_type","claim_file")
								->where("table_id",$claimFileID)
								->get();
		foreach ($tickets as $key2 => $ticket) {
			$data['detail']['ticketsdetail'][$key1+$key2+1]['id']=$ticket['id'];
    		$data['detail']['ticketsdetail'][$key1+$key2+1]['description']=$ticket['ticket_cat']['display_name'];
		}

		$this->novaMessage->setData($data);
		return $this->returnJSONMessage();

     }catch(ModelNotFoundException $ex){
        	$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
           }

        return $this->returnJSONMessage();
    }


}
