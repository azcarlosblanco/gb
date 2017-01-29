<?php namespace Modules\InsuranceCompany\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceCompanyOffice extends Model {

	protected $table = "insurance_company_office";

    protected $fillable = ["office_name","representative","email","country",
    						"state","city","address","default","insurance_company_id"];

     //to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    
    /**
     * Function Model: insuranceCompany
     * Description: Returns the insurance company to which the office belong
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return InsuranceCompany
     */
    public function insuranceCompany() {
        return $this->belongsTo('Modules\InsuranceCompany\Entities\InsuranceCompany'); // this matches the Eloquent model
    }

    /**
     * Function Model:offices
     * Description: Define the relationship one to many between Insurance Companies and their offices
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return InsuranceCompanyOffice
     */
    public function phones() {
        return $this->hasMany('Modules\InsuranceCompany\Entities\InsuranceCompanyOfficePhone'); // this matches the Eloquent model
    }
}