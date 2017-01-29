<?php namespace Modules\InsuranceCompany\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceCompanyEmail extends Model {

	protected $table = "insurance_company_email";

    protected $fillable = [ 
                            'email',
                            'contact_name',
                            'reason',
                            'insurance_company_id',
                            'subject',
                            'template'
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
     * Function Model: insuranceCompany
     * Description: Returns the insuarance company to which the email belong
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return InsuranceCompany
     */
    public function insuranceCompany() {
        return $this->belongsTo('Modules\InsuranceCompany\Entities\InsuranceCompany'); // this matches the Eloquent model
    }

}