<?php namespace Modules\Dashboard\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use App\ProcedureEntry;
use App\ProcedureCatalog;
use App\ProcessCatalog;
use Carbon\Carbon;
use Modules\Policy\Entities\Policy;
use Modules\Payment\Entities\PolicyCost;
use Modules\Agente\Entities\Agente;

class DashboardController extends NovaController {


	public function procedureTime(Request $request){
		try{
			$input = $request->all(); 
		
			if(isset($input['procedure']) && $input['procedure']!=""){
				$procedure = $input['procedure'];
			}else{
				$procedure="newpolicy";
			}

			//by default the hitoric average
			$from=Carbon::create(1960, 1, 1, 0)->toDateTimeString();
			$to=Carbon::now()->toDateTimeString();
			if(isset($input['from']) && $input['from']!=""){
				$from = date("Y-m-d",strtotime($input['from']));
			}
			if(isset($input['to']) && $input['to']!=""){
				$to = date("Y-m-d",strtotime($input['to']));
			}

			$responsible="";
			if(isset($input['responsible_id']) && $input['responsible_id']!=""){
				$responsible = $input['responsible_id'];
			}

			$params['from'] = $from; 
			$params['to'] = $to;
			$params['responsible'] = $responsible;

			//seleccionar los tramites que ya han sido completados
			//sacar la diferencia entre start_date y end_date de cada proceso
			//sacar el average de la diferencia en los tiempos
			$list = ProcedureEntry::averageTimeByProcess($procedure,$params);

			$listProcessCat = ProcessCatalog::whereHas("procedureCatalog", 
												function ($query) use ($procedure)  {
												    $query->where('name', $procedure);
												})->select("seq_number","description","id")
												->get()
												->keyBy("id");
			//label
			//datavalues in seconds
			$data = array();

			$sortedList = collect($list)->sortBy("time_diff");
			/*$oder = $dataCollection
			print_r($oder);*/

			foreach ($list as $key => $value) {
				$data[$key]["time"] = $value->time_diff + 0;
				$data[$key]["name"] = $listProcessCat[$value->process_catalog_id]['description'];
				$data[$key]["seq"] =$listProcessCat[$value->process_catalog_id]['seq_number'];
			}

			$code = 200;
			$this->novaMessage->setData($data);
			return $this->returnJSONMessage($code);
		}catch(\Exception $e){
			$code = 500;
			$this->novaMessage->addErrorMessage('Error', $e->getMessage());
			return $this->returnJSONMessage($code);
		}
	}

	public function policiesSales(Request $request){
		try{
			$input = $request->all(); 
			//by default the sales in the last year
			$from = Carbon::now()->subYear();
			$from = $from->toDateTimeString();
			$to = Carbon::now()->toDateTimeString();
			if(isset($input['from']) && $input['from']!=""){
				$from = date("Y-m-d",strtotime($input['from']));
			}
			if(isset($input['to']) && $input['to']!=""){
				$to = date("Y-m-d",strtotime($input['to']));
			}

			$frequency = "year";
			if(isset($input['frequency']) && $input['frequency']!=""){
				$frequency=$input['frequency'];
			}

			$type="emision";
			if(isset($input['type']) && $input['type']!=""){
				$type=$input['type'];
			}


			$thisPeriod = $this->getSalesByMonth($from,$to,$type);

			$frompast = Carbon::createFromFormat("Y-m-d H:i:s",$from)->subYear();
			$frompast = $frompast->toDateTimeString();
			$topast = Carbon::createFromFormat("Y-m-d H:i:s",$to)->subYear();
			$topast = $topast->toDateTimeString();
			$lastPeriod = $this->getSalesByMonth($frompast,$topast,$type);

			$salesByMonth['current'] = $thisPeriod;
			$salesByMonth['past']    = $lastPeriod;
			
			$code = 200;
			$this->novaMessage->setData($salesByMonth);
			return $this->returnJSONMessage($code);
		}catch(\Exception $e){
			$code = 500;
			$this->novaMessage->addErrorMessage('Error', $e->getMessage());
			return $this->returnJSONMessage($code);
		}
	}

	private function getSalesByMonth($from,$to,$type){
		$query = \DB::table("policy_cost")	
							->join('policy',
									'policy_cost.policy_id','=',
									'policy.id');
		if($type=="emision"){
			$query = $query->where("policy.emision_number",1)
								->where("policy.renewal_number",0);
		}else{
			$query = $query->where("policy.emision_number",1)
								->where("policy.renewal_number",">",0);
		}
		$policies = $query->whereIn("policy.id",function($query){
								$query->select('policy_cost.policy_id')
					                      ->from('policy_cost')
										  ->where("policy_cost.quote_number",1)
										  ->where("policy_cost.state",PolicyCost::S_PAIDOFF);
							})
							->where("date_paidoff",">=",$from)
							->where("date_paidoff","<=",$to)
							->orderBy("date_paidoff")
							->select("policy.id","policy_cost.date_paidoff",
								\DB::RAW("SUM(policy_cost.total) as total_cost"))
							->groupBy('policy.id')
							->get();
		
		$salesByMonth = array();
		foreach ($policies as $policy){
			$pd = Carbon::createFromFormat('Y-m-d', $policy->date_paidoff);
			$month = $pd->month;
			if( isset($salesByMonth[$month]) ){
				$salesByMonth[$month] = $salesByMonth[$month] + $policy->total_cost;
			}else{
				$salesByMonth[$month] = $policy->total_cost;
			}
		}
		return $salesByMonth;
	}

	public function agentSales(Request $request){
		try{
			$input = $request->all(); 
			//by default the sales in the last year
			$from=Carbon::now()->subYear()->toDateTimeString();
			
			$to=Carbon::now()->toDateTimeString();
			if(isset($input['from']) && $input['from']!=""){
				$from = date("Y-m-d",strtotime($input['from']));
			}
			if(isset($input['to']) && $input['to']!=""){
				$to = date("Y-m-d",strtotime($input['to']));
			}

			$type="emision";
			if(isset($input['type']) && $input['type']!=""){
				$type=$input['type'];
			}

			$query = \DB::table("policy_cost")	
							->join('policy',
									'policy_cost.policy_id','=',
									'policy.id');
			if($type=="emision"){
				$query = $query->where("policy.emision_number",1)
									->where("policy.renewal_number",0);
			}else{
				$query = $query->where("policy.emision_number",1)
									->where("policy.renewal_number",">",0);
			}
			$policies = $query->whereIn("policy.id",function($query){
									$query->select('policy_cost.policy_id')
						                      ->from('policy_cost')
											  ->where("policy_cost.quote_number",1)
											  ->where("policy_cost.state",PolicyCost::S_PAIDOFF);
								})
								->where("date_paidoff",">=",$from)
								->where("date_paidoff","<=",$to)
								->orderBy("date_paidoff")
								->select("policy.id",
										"policy.agente_id",
										"policy_cost.date_paidoff",
										\DB::RAW("SUM(policy_cost.total) as total_cost"))
								->groupBy('policy.id','policy.agente_id')
								->get();
			
			$salesByAgent = array();
			$agents = Agente::select("name","lastname","id")->get();
			
			foreach ($agents as $agent) {
				$data['agents'][$agent->id] = $agent->full_name; 
			}
			foreach ($policies as $policy){
				if( isset($salesByAgent[$policy->agente_id]) ) {
					$salesByAgent[$policy->agente_id] = $salesByAgent[$policy->agente_id] + 
															$policy->total_cost;
				}else{
					$salesByAgent[$policy->agente_id] = $policy->total_cost;
				}
			}

			$data['sales']  = $salesByAgent;

			$code = 200;
			$this->novaMessage->setData($data);
			return $this->returnJSONMessage($code);
		}catch(\Exception $e){
			$code = 500;
			$this->novaMessage->addErrorMessage('Error', $e->getMessage());
			return $this->returnJSONMessage($code);
		}
	}

	private function numberToNameMonth(){
		switch ($number) {
			case 1:
				return "enero";
			case 2:
				return "febrero";
			case 3:
				return "marzo";
			case 4:
				return "abril";
			case 5:
				return "mayo";
			case 6:
				return "junio";
			case 7:
				return "julio";
			case 8:
				return "agosto";
			case 9:
				return "septiembre";
			case 10:
				return "octubre";
			case 11:
				return "noviembre";
			case 12:
				return "diciembre";
		}
	}

	public function getResumeNewPolicies(Request $request){

	}

	//can be order sub requested by client and in a perid of time
	public function getClaimByConcept(){

	}

	public function getResumeRenewals(){

	}
	

}
