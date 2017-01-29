<?php namespace Modules\InsuranceCompany\Entities;
   
use Illuminate\Database\Eloquent\Model;

class InsuranceCompanyPhone extends Model {

	protected $table = "insurance_company_phone";

    protected $fillable = ["country_code","area_code","number",
    						"default","insurance_company_office_id"];

    /**
     * Function Model: insuranceCompany
     * Description: Returns the insuarance company Office to which the phone belong
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return InsuranceCompany
     */
    public function insuranceCompany() {
        return $this->belongsTo('Modules\InsuranceCompany\Entities\InsuranceCompanyOffice'); // this matches the Eloquent model
    }

}