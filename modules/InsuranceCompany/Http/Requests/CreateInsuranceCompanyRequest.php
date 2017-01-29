<?php
namespace Modules\InsuranceCompany\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Request;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\InsuranceCompany\Entities\InsuranceCompanyOffice;

class CreateInsuranceCompanyRequest extends Request
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
}
?>