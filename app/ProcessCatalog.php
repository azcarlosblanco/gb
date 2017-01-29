<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessCatalog extends Model
{
	protected $table='process_catalog';

	protected $fillable=['name',
                         'department', //role that must perform the process
						 'procedure_catalog_id',
						 'next_process',
						 'group',   //
						 'seq_number',
                         'icon', //icono del action buton que representa el proceso
                         'link', //link to open when we give want to do the process
						 'last_process'
                         ];

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Method: procedureCatalog
     * Description: This method links the process to one procedure
     * Date created: 04-05-2016
     * Cretated by : Rocio Mera S
     *               [rociom][@][novatechnology.com.ec]
     */
    public function procedureCatalog()
    {
        return $this->belongsTo('App\ProcedureCatalog','procedure_catalog_id','id');
    }

    /**
     * Method: processEntry
     * Description: This method links the process to the processEntry that are of the same type
     * Date created: 04-05-2016
     * Cretated by : Rocio Mera S
     *               [rociom][@][novatechnology.com.ec]
     */
    public function processEntry()
    {
    	return $this->hasMany('App\ProcessEntry','process_entry_id','id');
    }

    public function department()
    {
        return $this->belongsTo('Modules\Authorization\Entity\Role','department','id');
    }

    public function prerequesiteID(){
        $prerequesites=\DB::table('process_prerequisite')
            ->where('prs_cat_id',$this->id)
            ->pluck('pre_prs_cat_id');

        return $prerequesites;
    }

    public function prerequesite()
    {
        $prerequesites=$this->prerequesiteID();
        $processes=ProcessCatalog::whereIn('id',$prerequesites)
                    ->get();
        return $processes;
    }

    public function getNextProcesses(){
        $prerequesites=\DB::table('process_prerequisite')
            ->where('pre_prs_cat_id',$this->id)
            ->pluck('prs_cat_id');

        $processes=ProcessCatalog::whereIn('id',$prerequesites)
                    ->get();

        return $processes;
    }

    /*
     * This function return true if the process is compulsory
     */
    public function isCompulsory()
    {
        if($this->compulsory==1){
            return true;
        }else{
            return false;
        }
    }
}
?>
