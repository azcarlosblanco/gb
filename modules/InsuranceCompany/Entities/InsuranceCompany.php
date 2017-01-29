<?php namespace Modules\InsuranceCompany\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Modules\InsuranceCompany\Entities\InsuranceCompanyEmail;
use Modules\InsuranceCompany\Entities\InsuranceCompanyOffice;
use Modules\InsuranceCompany\Entities\InsuranceCompanyPhone;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceCompany extends Model {

	//TABLE NAME RELATIONSHIP
	protected $table = "insurance_company";

	// MASS ASSIGNMENT ATTRIBUTES
    protected $fillable = array('company_name', 'representative');

    //to enable soft delete in the model
    use SoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Function Model:offices
     * Description: Define the relationship one to many between Insurance Companies and their offices
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return InsuranceCompanyOffice
     */
    public function offices() {
        return $this->hasMany('Modules\InsuranceCompany\Entities\InsuranceCompanyOffice'); // this matches the Eloquent model
    }

    /**
     * Function Model:emails
     * Description: Define the relationship one to many between Insurance Companies and the emails to contact them
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return InsuranceCompanyEmails
     */
    public function emails() {
        return $this->hasMany('Modules\InsuranceCompany\Entities\InsuranceCompanyEmail'); // this matches the Eloquent model
    }

}