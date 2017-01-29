<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\ReceptionNewPoliza\Entities\ProcessInitialDocumentation;
use App\ProcessCatalog;
use JWTAuth;
use Carbon\Carbon;

/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcedureEntry extends Model
{

	protected $table='procedure_entry';

	protected $fillable=[
						 'procedure_catalog_id',
						 'start_date',
						 'end_date',
						 'state',
						 'responsible', //no segura si debe ir esto aqui
						 'policy_id'
						 ];

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    private $procedureCatalogName='';

    function __construct($procedureCatalogName='',array $attributes = array()){
    	$this->procedureCatalogName=$procedureCatalogName;
    	parent::__construct($attributes);
    }

	/**
	 * Method: procedureCatalog
	 * Description: This method links the instance with a procedureCatalog
	 * Date created: 04-05-2016
	 * Cretated by : Rocio Mera S
	 *               [rociom][@][novatechnology.com.ec]
	 */
	public function procedureCatalog(){
		return $this->belongsTo('App\ProcedureCatalog','procedure_catalog_id','id');
	}

	/**
	 * Method: processEntry
	 * Description: This method links the process with his procedure
	 * Date created: 04-05-2016
	 * Cretated by : Rocio Mera S
	 *               [rociom][@][novatechnology.com.ec]
	 */
	public function processEntry(){
		return $this->hasMany('App\ProcessEntry','procedure_entry_id','id');
	}

	/**
	 * Method: poliza
	 * Description: This method links the procedure to one poliza
	 * Date created: 04-05-2016
	 * Cretated by : Rocio Mera S
	 *               [rociom][@][novatechnology.com.ec]
	 */
	public function policy(){
		return $this->belongsTo('Modules\Policy\Entities\Policy','policy_id','id');
	}

	/**
	 * Method: poliza
	 * Description: This method links the procedure to one user
	 * Date created: 04-05-2016
	 * Cretated by : Rocio Mera S
	 *               [rociom][@][novatechnology.com.ec]
	 */
	public function responsible(){
		return $this->belongsTo('App\User','responsible','id');
	}
	public function csEmergency(){
		return $this->hasMany('Modules\ClientService\Entities\Emergency','procedure_entry_id','id');
	}

	public function hospitalization(){
		return $this->hasMany('Modules\ClientService\Entities\Hospitalization','procedure_entry_id','id');
	}

	public function claims(){
        return $this->belongsToMany('Modules\Claim\Entities\Claim',
        	'claim_procedure',
            'procedure_entry_id',
            'claim_id');
    }

    public function proceddureCancellation(){
    	return $this->hasOne("App\ProcedureCancellation","procedure_entry_id");
    }


	public function pendiente($query){
		return $query->where('state','!=','finished')
						->where('state','!=','cancelled');
	}

    public function scopePending($query)
    {
        return $query->where('state','!=','finished')
						->where('state','!=','cancelled');
    }

	/*
	 * MODEL FUNCTIONS
	 */

	/*
	 * Method: start
	 * This function must be called when a procedure start.
	 * This function create a register in the table procedure_entry
	 * and call the fisrt process in the procedure
	 * Input: $input [procedureCatalogName]
	 * Output: proceduryEntry
	 * Date created: 04-05-2016
	 * Cretated by : Rocio Mera S
	 *               [rociom][@][novatechnology.com.ec]
	 */
	public function start()
	{
		//get the catalog to which it belong
		$carbon = new \Carbon\Carbon();
		$date = $carbon->now();

		$procedureCatalog = ProcedureCatalog::
									where('name',$this->procedureCatalogName)
									->firstOrFail();

		//create the register of the procedure in the table
		$this->procedure_catalog_id=$procedureCatalog->id;
		$this->start_date=$date;
		$this->state='inprocess';
		$this->save();
	}

	public function finish(){
		$carbon = new \Carbon\Carbon();
		$date = $carbon->now();

		$this->end_date=$date;
		$this->state='finished';
		$this->save();
	}

	public function cancel($reason, $responsible){

		//can only cancel a procedure is this procedure is active
		if( ! $this->isActive() ){
			throw new \Exception("El trámite no está activo");
		}

		switch ($this->procedureCatalog->name) {
			case 'newpolicy':
				$this->cancelNewPolicy();
				break;
			case 'claim':
				$this->cancelClaim();
				break;
			case 'settlement':
				$this->cancelSettlement();
				break;
			case 'renewal':
				$this->cancelRenewal();
				break;
			case 'clientservice':
				$this->cancelClientService();
				break;
			default:
				# code...
				break;
		}

		$carbon = new \Carbon\Carbon();
		$date = $carbon->now();

		//save why the procedure is cancelled
		\App\ProcedureCancellation::create([
				"reason"             => $reason,
				"responsible_id"     => $responsible,
				"procedure_entry_id" => $this->id
			]);

		//cancel the current process
		foreach ($this->getCurrentProcesses() as $process) {
			$process->cancel();
		}

		//who did the action??? where to add that
		$this->end_date=$date;
		$this->state='cancelled';
		$this->save();
	}

	private function cancelNewPolicy(){
		//if policy already exist we have to mark this policy has cancelled and
		//delete logic the element associate with that policy

		//before delete teh policy save the information about the application
		$policy = $this->policy;
		if( $policy != null ){
			//delete affiliate_policy
			//delets affiliate_policy_annexe
			//delste affiliate_policy_deducible
			//delete affiliate_policy_extras
			//policy_cost                |
			//policy_cost_detail         |
			//delete cheque_payment_detail, credit_card_payment_detail, deposit_payment_detail,
			//policy_cost_tax_fees       |
			//policy_deducible           |
			//policy_discount            |
			//delete customer (solo si el customer no esta asociado a ninguna poliza)
			//delete affiliate
			//delte policy               |
			$carbon = new Carbon();
			$affpolicies = $policy->affiliatesAll()->with('affiliate')->get();

			foreach ($affpolicies as $affpolicy) {
				$aff = $affpolicy->affiliate;

				$annexs = $affpolicy->annex()->pluck('id');
				\DB::table((new \Modules\Affiliate\Entities\AffiliatePolicyAnnex)->getTable())
						->whereIn('id',$annexs)->update(['deleted_at'=>(new Carbon())]);

				$extras = $affpolicy->anmend()->pluck('id');
				\DB::table((new \Modules\Affiliate\Entities\AffiliatePolicyExtra)->getTable())
						->whereIn('id',$extras)->update(['deleted_at'=>(new Carbon())]);

				$deducts = $affpolicy->deducibles()->pluck('id');
				\DB::table((new \Modules\Affiliate\Entities\AffiliatePolicyDeducible)->getTable())
						->whereIn('id',$deducts)->update(['deleted_at'=>(new Carbon())]);

				//can not delete the affiliate, unless the affiliate was created just for this policy, and it does not have other policies associated to him
				$count = $aff->affiliatePolicyAll()->count();

				$affpolicy->delete();

				if($count == 1){
					$aff->delete();
				}
			}


			$policyDeducible = $policy->deducibles()->pluck('id');
			\DB::table((new \Modules\Policy\Entities\PolicyDeducible)->getTable())
					->whereIn('id',$policyDeducible)->update(['deleted_at'=>(new Carbon())]);


			$policyCosts = $policy->policyCost;
			foreach ($policyCosts as $policyCost) {
				$pcd = $policyCost->policyCostDetails()->pluck('id');
				\DB::table((new \Modules\Payment\Entities\PolicyCostDetail)->getTable())
						->whereIn('id',$pcd)->update(['deleted_at'=>(new Carbon())]);

				$paymentgrp = $policyCost->getPaymentMethods();
				foreach ($paymentgrp as $payments) {
					foreach ($payments as $payment) {
						$payment->delete();
					}
				}

				$policyCost->delete();
			}

			$prevIns = $policy->prevInsurance;
			if($prevIns!=null){
				$prevIns->delete();
			}

			$pDisc = $policy->discounts()->first();
			if($pDisc!=null){
				$pDisc->delete();
			}


			//detele the custome if the customer only have this policy associted to him
			//and have not had other policies associated to him before
			$customer = $policy->customer;
			$num = $customer->policiesAll()->count();
			if($num==1){
				$customer->delete();
			}

			//delte tickets related to the policy
			$tickets = $policy->ticket;
			foreach ($tickets as $ticket) {
				$det = $ticket->ticket_detail()->pluck('id');
				\DB::table((new \Modules\ClientService\Entities\TicketDetail)->getTable())
						->whereIn('id',$det)->update(['deleted_at'=>(new Carbon())]);
				$ticket->delete();
			}

			$policy->delete();
		}
	}

	private function cancelClaim(){
		/*
			| claim                      |
			| claim_file                 |
			| claim_procedure            |
			| ticket , relate to the claim |
		*/

		$claims = $this->claims;
		foreach ($claims as $claim) {
			$files = $claim->files()->pluck('id');
			\DB::table((new \Modules\Claim\Entities\ClaimFile)->getTable())
					->whereIn('id',$files)->delete();
			$claim->delete();
		}

		\DB::table("claim_procedure")
				->where("procedure_entry_id",$this->id)
				->delete();

		$tickets = \Modules\ClientService\Entities\Ticket::where("table_type","procedure_entry")
							->where("table_id",$this->id);
		foreach ($tickets as $ticket) {
			$det = $ticket->ticket_detail()->pluck('id');
			\DB::table((new \Modules\ClientService\Entities\TicketDetail)->getTable())
						->whereIn('id',$det)->delete();
			$ticket->delete();
		}
	}

	private function cancelSettlement(){

		/*
		| claim_settlement           |
		| claim_settlement_refund    |
		*/
		$claims = $this->claims;
		foreach ($claims as $claim) {
			$files = $claim->files()->pluck('id');

			$setts = \DB::table((new \Modules\Claim\Entities\ClaimSettlement)->getTable())
										->whereIn('claim_file_id',$files)
										->pluck('id');

			$sett_refund = \DB::table(
								(new \Modules\Claim\Entities\ClaimSettlementRefund)->getTable())
										->whereIn('claim_settlement_id',$setts)
										->pluck('id');

			\DB::table((new \Modules\Claim\Entities\ClaimSettlementRefund)->getTable())->whereIn('id',$sett_refund)->delete();
			\DB::table((new \Modules\Claim\Entities\ClaimSettlement)->getTable())->whereIn('id',$setts)->delete();
			\DB::table((new \Modules\Claim\Entities\ClaimFile)->getTable())->whereIn('id',$files)->delete();

			$tickets = \Modules\ClientService\Entities\Ticket::
								where("table_type",ClaimSettlement::getTable())
								->whereIn("table_id",$setts);
			foreach ($tickets as $ticket) {
				$det = $ticket->ticket_detail()->pluck('id');
				\DB::table(\Modules\ClientService\Entities\TicketDetail::getTable())->whereIn('id',$det)->delete();
				$ticket->delete();
			}
		}

		\DB::table("claim_procedure")
				->where("procedure_entry_id",$this->id)
				->select('id')
				->delete();

		$tickets = \Modules\ClientService\Entities\Ticket::where("table_type","procedure_entry")
							->where("table_id",$this->id);
		foreach ($tickets as $ticket) {
			$det = $ticket->ticket_detail()->pluck('id');
			\DB::table((new \Modules\ClientService\Entities\TicketDetail)->getTable())
						->whereIn('id',$det)->delete();
			$ticket->delete();
		}
	}

	private function cancelRenewal(){

	}

	private function cancelClientService(){

	}


	public function getCurrentProcesses(){
		$model=ProcessEntry::where("procedure_entry_id",$this->id)
			->where('process_entry.state','!=','finished')
			->where('process_entry.state','!=','cancelled')
			->get();
		return $model;
	}

	public function getLastActiveProcess(){
		$model=ProcessEntry::where("procedure_entry_id",$this->id)
			->where('process_entry.state','!=','finished')
			->where('process_entry.state','!=','cancelled')
			->orderBy('id', 'desc')
			->first();
		return $model;
	}

	public static function getListProcedures($procedureCatalogName,
												$userID='',
												$search='')
	{
		$query=\DB::table('procedure_catalog')
							->join('procedure_entry',
								'procedure_catalog.id','=',
								'procedure_entry.procedure_catalog_id')
							->join('process_entry',
								'procedure_entry.id','=',
								'process_entry.procedure_entry_id')
							->join('user',
								'process_entry.responsible','=',
								'user.id')
							->where('procedure_catalog.name',
								$procedureCatalogName)
							->select('procedure_entry.id',
					'procedure_entry.start_date as pcd_start_date',
					'procedure_entry.end_date as pcd_end_date',
					'procedure_entry.policy_id as policy_id',
					'process_entry.id as current_process_id',
					'process_entry.start_date as prs_start_date',
					'process_entry.end_date as prs_end_date',
					'process_entry.process_catalog_id',
					'user.id as u_id',
					'user.name as u_name',
					'user.lastname as u_lastname',
					'user.email as u_email');

		if($userID!=''){
			$query->where('process_entry.responsible',$userID);
		}

		return $query;
	}

	public static function getListFinishedProcedures(
									$procedureCatalogName,
									$userID='',
									$search='')
	{
		$query=self::getListProcedures($procedureCatalogName,
									$userID,
									$search);
		$query->where('procedure_entry.state','finished')
				->orWhere('procedure_entry.state','cancelled');

		$listProcedures=$query->orderBy('pcd_start_date','id','current_process_id')
							->get();

		return $listProcedures;
	}

	public static function getListPendingProcedure(
									$procedureCatalogName,
									$userID='',
									$search=''){
		$query=self::getListProcedures($procedureCatalogName,
									$userID,
									$search);
		$query->where('procedure_entry.state','inprocess')
				->where('process_entry.state','!=','finished')
				->where('process_entry.state','!=','cancelled');

		$listProcedures=$query->orderBy('procedure_entry.start_date')
							->distinct('process_entry.id')
							->get();

		return $listProcedures;
	}

	public static function averageTimeByProcess($procedureCatalogName,$params)
	{
		$query=\DB::table('procedure_catalog')
							->join('procedure_entry',
								'procedure_catalog.id','=',
								'procedure_entry.procedure_catalog_id')
							->join('process_entry',
								'procedure_entry.id','=',
								'process_entry.procedure_entry_id')
							->where('procedure_catalog.name',
								$procedureCatalogName)
							->select(
								\DB::RAW('AVG(TIMESTAMPDIFF(MINUTE,process_entry.start_date,process_entry.end_date)) as time_diff'),
									'process_entry.process_catalog_id'
							);

		if($params['responsible']!=''){
			$query->where('process_entry.responsible',$params['responsible']);
		}


		$query->where('process_entry.start_date', '>=' ,$params['from']);
		$query->where('process_entry.end_date', '<=' ,$params['to']);


		$query->where('procedure_entry.state','finished');

		$listProcedures=$query->orderBy('process_entry.process_catalog_id')
									->groupBy('process_entry.process_catalog_id')
									->get();

		return $listProcedures;
	}

	/**
	 * Tis function return a list of php std objects whose id are the ID of
	 * the procedure that are pending and must be executed by the user
	 * identify by the parameter $userID
	 * @param  [type] $procedureCatalogName [description]
	 * @param  [type] $userID               [description]
	 * @return [type]                       [description]
	 */
	/*public static function getPendingProcedure($procedureCatalogName,
												$userID=''){
		$query=\DB::table('procedure_catalog')
							->join('procedure_entry',
								'procedure_catalog.id','=',
									'procedure_entry.procedure_catalog_id')
							->join('process_entry',
								'procedure_entry.id','=',
									'process_entry.procedure_entry_id')
							->join('users',
								'process_entry.responsible','=',
								'users.id')
							->where('procedure_catalog.name',
								$procedureCatalogName)
							->where('procedure_entry.state','inprocess')
							->where('process_entry.state','!=','finished')
							->where('process_entry.state','!=','cancelled')
							->select('procedure_entry.id'
									,'process_entry.id as current_process_id');

		if($userID!=''){
			$query->where('process_entry.responsible',$userID);
		}
		$listProcedures=$query->orderBy('process_entry.start_date')
							->distinct('process_entry.id')
							->get();

		return $listProcedures;
	}*/

	public function getListActionButtons($departmentID){
		//process that belong to the procedure
		$pCatalog = $this->procedureCatalog;
		$pCatalog->load('processCatalog');
		//print_r($pCatalog);
		$listBt = $pCatalog->processCatalog()
						->select(
								'id',
								'name',
								'icon',
								'link',
								'description',
								'compulsory'
								)
						->where('department',$departmentID)
						->where('display',1)
						->orderBy('seq_number')
						->get();
		//print_r($listBt);
		return $listBt;
	}

	public function getListButtons($listBt,
							$process_ID,
							$cu_process_catalog_id){
		$result=[];
		$index=0;

		$processes=ProcessEntry::where("procedure_entry_id",$this->id)
			->where('state','!=','cancelled')
			->where('state','!=','realocated')
			->get()
			->keyBy('process_catalog_id')
			->toArray();


		$listID=array_keys($processes);
		foreach ($listBt as $button) {
			$catID=$button->id;

			if(in_array($catID, $listID) ){
				//process that have been done or are in process
				if($processes[$catID]['state']=='finished'){
					$result['buttons'][$index]['class']='used';
				}else{
					$result['buttons'][$index]['class']='available';
					$result['current_description']=$button->description;
				}
				$result['buttons'][$index]['active']=true;
				$result['buttons'][$index]['link']=$button->link;
				$result['buttons'][$index]['params']
							=[
							  'process_ID'   => $processes[$catID]['id'],
							  'procedure_ID' => $processes[$catID]['procedure_entry_id']
							  ];
			}else{
				$result['buttons'][$index]['active']=false;
				$result['buttons'][$index]['class']='unavailable';
				$result['buttons'][$index]['params'] = [];
			}

			$result['buttons'][$index]['name']=$button->name;
			$result['buttons'][$index]['description']=$button->description;
			$result['buttons'][$index]['icon']=$button->icon;
			$index++;
		}
		return $result;
	}

	public function isActive(){
		return ( ($this->state != 'finished') && ($this->state != 'cancelled') );
	}

	/**
     * This function is similar to the function getListButtons, only it use the relationship to
     * access the list of process of teh procedure.
     * If the procedure use eager loading to query the realionship, this function should be
     * faster because we will no access the database, we would be using the eager loading
     * data
	 */
	public function getListButtonsV2($listBt){
		$result=[];
		$index=0;

		$processes=$this->processEntry()->where('state','!=','cancelled')
										->where('state','!=','realocated')
										->get()
										->keyBy('process_catalog_id')
										->toArray();

		$listID=array_keys($processes);

		foreach ($listBt as $button) {
			$catID=$button->id;

			if(in_array($catID, $listID) ){
				//process that have been done or are in process
				if($processes[$catID]['state']=='finished'){
					$result['buttons'][$index]['class']='used';
				}else{
					$result['buttons'][$index]['class']='available';
					$result['current_description']=$button->description;
				}
				$result['buttons'][$index]['active']=true;
				$result['buttons'][$index]['link']=$button->link;
				$result['buttons'][$index]['params']
							=[
							  'process_ID'   => $processes[$catID]['id'],
							  'procedure_ID' => $processes[$catID]['procedure_entry_id']
							  ];
			}else{
				$result['buttons'][$index]['active']=false;
				$result['buttons'][$index]['class']='unavailable';
				$result['buttons'][$index]['params'] = [];
			}

			$result['buttons'][$index]['name']=$button->name;
			$result['buttons'][$index]['description']=$button->description;
			$result['buttons'][$index]['icon']=$button->icon;
			$index++;
		}
		return $result;
	}

}
?>
