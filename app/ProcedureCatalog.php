<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcedureCatalog extends Model
{

	protected $table='procedure_catalog';
	
	protected $fillable=['name','description'];

	//to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

	/**
	 * Method: procedures
	 * Description: This method return the procedure that bolong to this type
	 * Date created: 03-05-2016
	 * Cretated by : Rocio Mera S
	 *               [rociom][@][novatechnology.com.ec]
	 */
	public function procedureEntry()
	{
		return $this->hasMany('App\ProcedureEntry','procedure_catalog_id','id');
	}

	/**
	 * Method: procedures
	 * Description: This method return the procedure that bolong to this type
	 * Date created: 03-05-2016
	 * Cretated by : Rocio Mera S
	 *               [rociom][@][novatechnology.com.ec]
	 */
	public function processCatalog()
	{
		return $this->hasMany('App\ProcessCatalog','procedure_catalog_id','id');
	}

	/**
	 * Method: getFirstProcessCatalog
	 * Description: This method fisrt process that must be executed where a procedure start
	 * Output: Return a instance of procedureCatalog
	 * Date created: 03-05-2016
	 * Cretated by : Rocio Mera S
	 *               [rociom][@][novatechnology.com.ec]
	 */
	public function getFirstProcessCatalog()
	{
		return $this->processCatalog()->where('seq_number',1)->firstOrFail();
	}
	

}
?>