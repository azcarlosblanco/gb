<?php
namespace Modules\InsuranceCompany\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Request;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\InsuranceCompany\Entities\InsuranceCompanyOffice;

class UpdateInsuranceCompanyRequest extends Request
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
            'company_name'    =>  'required',
            'representative'  =>  'required',
        ];
    }

    public function getValidatorInstance() {

        $validator = parent::getValidatorInstance();

        $validator->after(function() use ($validator) {
            $id = $this->route('id');

            try{
                $insuranceCompany=InsuranceCompany::findOrFail($id);
            }catch(ModelNotFoundException $ex){
                $validator->errors()->add('office', 'Insurance Company does not exist');
            }
        });

        return $validator;
    }
}
?>