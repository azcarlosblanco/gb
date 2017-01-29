<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\ProcessCatalog;
use App\ProcedureCatalog;
use App\ProcedureEntry;
use Modules\Reception\Entities\ProcessInitialDocumentation;
use Modules\Emission\Entities\ProcessUploadPolicyRequest;
use Modules\Emission\Entities\ProcessRequestAppNewPolicyBD;
use Modules\Emission\Entities\ProcessReviewProspectPolicy;
use Modules\Emission\Entities\ProcessSendProspectPolicy;
use Modules\Emission\Entities\ProcessRegisterCustomerResponse;
use Modules\Authorization\Entities\Role;
use App\User;
use JWTAuth;

/**
 * Class: ProcessEntry
 * Description: This class represent a instance of a process that is part of a procedure
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessEntry extends Model
{
	protected $table='process_entry';

	protected $fillable=['process_catalog_id',
                         'procedure_entry_id',
						 'start_date',
						 'end_date',
						 'state',
						 'responsible']; //user_id

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    //MODEL VARIABLES
    private $processCatalog;

    private $procedureEntry;

    function __construct(array $attributes = array(),$processCatalogName=''){
        parent::__construct($attributes);
        if($processCatalogName!=''){
            $this->processCatalog =
                    ProcessCatalog::where('name',$processCatalogName)->firstOrFail();

            if($this->isFirstProcess()){
                $procedureCatalogName=$this->processCatalog->procedureCatalog->name;
                $this->procedureEntry=new ProcedureEntry($procedureCatalogName);
            }
        }
    }

    public static function newFromModel(Model $object){
        $processCatalog=ProcessCatalog::find($object->process_catalog_id);
        $process=ProcessFactory::createProcess($processCatalog->name);
        $process->setRawAttributes($object->attributes,true);
        $process->exists=1;
        return $process;
    }

    public static function findProcess($id, $columns = array('*'))
    {
				//TODO validate catalog
        $result = parent::find($id, $columns);
        if($result!=null){
            $result->processCatalog = ProcessCatalog::findOrFail($result->process_catalog_id);
        }
        return $result;
    }


    public function setProcedureEntry($procedureEntry){
        $this->procedureEntry=$procedureEntry;
    }


    /*
     * Return the Procedure Objects that are associated with the process
     * Date created: 03-05-2016
     * Cretated by : Rocio Mera S
     *               [rociom][@][novatechnology.com.ec]
     */
    public function processCatalogRel(){
        return $this->belongsTo('App\ProcessCatalog','process_catalog_id','id');
    }

    /*
     * Return the Procedure Objects that are associated with the process
     * Date created: 03-05-2016
 	 * Cretated by : Rocio Mera S
 	 *               [rociom][@][novatechnology.com.ec]
     */
    public function procedureEntryRel(){
		return $this->belongsTo('App\ProcedureEntry','procedure_entry_id','id');
    }

    /*
     * Return the Polizas Objects that are associated with the process
     * Date created: 03-05-2016
 	 * Cretated by : Rocio Mera S
 	 *               [rociom][@][novatechnology.com.ec]
     */
    public function poliza(){
    	return $this->belongsToManyThrough('Modules\Policy\Entities\Policy',
                                        'App\ProcedureEntry',
        								'procedure_entry_id',
                                        'policy_id');
    }

    /*
     * Query the process that has a especific state
     * Date created: 03-05-2016
 	 * Cretated by : Rocio Mera S
 	 *               [rociom][@][novatechnology.com.ec]
     */
    public function responsible(){
		return $this->belongsTo('App\User','responsible');
    }

    public function guiaEnvio()
    {
        return $this->belongsToMany('App\GuiaEnvio','guia_remision_process',
            'process_id','guia_remision_id');
    }

    /*
     * Query the process that has a especific state
     * Date created: 03-05-2016
 	 * Cretated by : Rocio Mera S
 	 *               [rociom][@][novatechnology.com.ec]
     */
    public function scopeState($query, $state){
    	return $query->where('state',$state);
    }

    /*
     * Query the process by the user that is responsible of the process
     * Date created: 03-05-2016
 	 * Cretated by : Rocio Mera S
 	 *               [rociom][@][novatechnology.com.ec]
     */
    public function scopeResponsible($query, $responsible){
    	return $query->where('responsible',$responsible);
    }

    public function getStatesProcess(){
    	return [
    			'inprocess'   => 'En Proceso',
                'finished'    => 'Terminado',
                'ticket'      => 'ConTicket',
                'realocated'  => 'Reasignado',
                'cancelled'   => 'Cancelado'
                ];
    }

    /*
     * MODEL FUNCTIONS
     */

    public function getProcessCatalog(){
        return $this->processCatalog;
    }

    /*public function getNextProcess(){
        if($this->hasNextProcess()){
            if($this->nextProcess==null){
                $nameProcess=$this->processCatalog->nextProcess->name;
                $this->nextProcess = ProcessFactory::
                                        createProcess($nameProcess);
            }else{
                $this->nextProcess;
            }
            return $this->nextProcess;
        }else{
            return null;
        }
    }*/

    /*
     * This function return true if the process is the first process of
     * a Procedure
     */
    public function isFirstProcess()
    {
        if($this->processCatalog->seq_number==1){
            return true;
        }else{
            return false;
        }
    }


    /*
     * This function return true if the process is the first process of
     * a Procedure
     */
    public function isLastProcess()
    {
        if($this->processCatalog->last_process==1){
            return true;
        }else{
            return false;
        }
    }

    /*
     * Method: start
     * This function must be called when a process start.
     * This function create a register in the table process_entry
     * Input: $input [responsible       =>Id user who must perform the process,
     *                procedure_entry_id=>Id procedure_entry to which this
     *                                    process belong ]
     * Output: proceduryEntry
     * Date created: 04-05-2016
     * Cretated by : Rocio Mera S
     *               [rociom][@][novatechnology.com.ec]
     */
    public function start($procedureEntry=null)
    {

        //if the process is the first process of a procedure we must start a register in the procedure_entry table
        if($this->isFirstProcess()){
            $this->procedureEntry->start();
        }else{
            if(isset($procedureEntry)){
                $p=ProcedureEntry::find($procedureEntry->id)
                                    ->where('state','!=','finished')
                                    ->where('state','!=','cancelled')
                                    ->select('id')
                                    ->get();
                if(isset($p)){
                    $this->setProcedureEntry($procedureEntry);
                }else{
                    throw new \Exception("Procedure does not Exist", 15);
                }
            }else{
                throw new \Exception("Procedure does not Exist", 15);
            }
        }

        $carbon = new \Carbon\Carbon();
        $date = $carbon->now();

        $this->responsible        = $this->getResponsibleID();
        $this->procedure_entry_id = $this->procedureEntry->id;
        $this->process_catalog_id = $this->processCatalog->id;
        $this->start_date         = $date;
        $this->state              = 'inprocess';
        $this->save();
    }

    public function finish(){
        if($this->state!='finished'){
            $carbon = new \Carbon\Carbon();
            $date = $carbon->now();

            $this->end_date  = $date;
            $this->state     = 'finished';
            $this->save();

            //if next_process is set, set the state of corresponding procedure_entry
            //to
            //to continue with the procedure
            if(is_null($this->procedureEntry) || $this->procedureEntry->id==null){
                $this->procedureEntry=$this->procedureEntryRel;
            }
            if(is_null($this->processCatalog) || $this->processCatalog->id==null){
                $this->processCatalog=$this->processCatalogRel;
            }

            if($this->isLastProcess()){
                //finish the procedure
                $this->procedureEntry->finish();
            }else{
                $nextProcessesCat=$this->processCatalog->getNextProcesses();
                foreach ($nextProcessesCat as $key => $processCat) {

                    $pr=$this->getProcessByCat($processCat->id);
                    if(isset($pr) &&
                        $pr->state!='cancelled'){
                        //can not start a process that is been doing or has been finished
                            continue;
                    }

                    if($processCat->isCompulsory()){
                        $process = ProcessFactory::
                                        createProcess($processCat->name);
                        if($process->
                            checkPrerequistite($this->procedureEntry)
                            ){
                            $process->start($this->procedureEntry);
                        }
                    }
                }
            }
        }
    }

    private function getProcessByCat($processCatId){
        $process=ProcessEntry::
                    where('procedure_entry_id',$this->procedure_entry_id)
                    ->where('process_catalog_id',$processCatId)
                    ->first();
        return $process;
    }

    /**
    * This function return true is all the prerequistes of the process have been fulfilled
    */
    public function checkPrerequistite($ProcedureEntry){
        $listCompleteProcess=ProcessEntry::
                                where('procedure_entry_id',$ProcedureEntry->id)
                                ->where('state','finished')
                                ->pluck('process_catalog_id')
                                ->toArray();

        $prerequistes=$this->processCatalog->prerequesiteID();

        $diff=array_diff($prerequistes,$listCompleteProcess);

        if(count($diff)==0){
            return true;
        }else{
            return false;
        }
    }

    //TODO: ADD a comment table
    public function cancel($comment=''){
        $carbon = new \Carbon\Carbon();
        $date = $carbon->now();

        $this->end_date           = $date;
        $this->state              = 'cancelled';
        $this->update();
    }

	public function isFinished(){
		return $this->state == 'finished';
	}

	public function isActive(){
		return ( ($this->state != 'finished') && ($this->state != 'cancelled') );
	}

    public function getResponsibleID($current=true){
        //Select a user that belong to the role 
        //and use automatic asignation
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            $user = null;
        }
        
        if($user==null){
            $user=Role::where("name","administracion")
                                ->first()
                                ->users()
                                ->first();
        }

        if($current){
            return $user->id;
        }else{
            $department=$this->getProcessCatalog()->department;
            $users=Role::find($department)->users->toArray();
            if(count($users)==0){
                return $user->id;
            }else{
                $index=rand(0,count($users)-1);
                return $users[$index]['id'];
            } 
        }
        
    }


/*
    public function reasignResponsible();

    public function createTicket();

    public function closeTicket();
*/

}
