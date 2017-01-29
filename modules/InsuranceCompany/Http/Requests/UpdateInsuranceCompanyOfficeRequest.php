<?php
namespace Modules\InsuranceCompany\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Request;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\InsuranceCompany\Entities\InsuranceCompanyOffice;

class UpdateInsuranceCompanyOfficeRequest extends Request
{
	/**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'office_name'    =>  'required',
            'representative'  =>  'required',
            'email'  =>  'required|email',
            'phone'  =>  'required',
            'country'  =>  'required',
            'state'  =>  'required',
            'city'  =>  'required',
            'address'  =>  'required'
        ];
    }

    public function getValidatorInstance() {

        $validator = parent::getValidatorInstance();

        $validator->after(function() use ($validator) {
            $id = $this->route('id');
            $id_office = $this->route('id_office');
            try{
                $insuranceCompany=InsuranceCompany::findOrFail($id);

                $insuranceCompanyOffice=InsuranceCompanyOffice::findOrFail($id_office);
            }catch(ModelNotFoundException $ex){
                $validator->errors()->add('office', 'Insurance Company does not exist');
            }
        });

        return $validator;
    }
}
?>