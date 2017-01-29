<?php
namespace Modules\Agente\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Request;
use Modules\Agente\Entities\Agente;

class CreateAgenteRequest extends Request
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
            'name'             => 'required',
            'lastname'         => 'required',
            'identity_document' => 'digits:10|required',
            'dob'               => 'required|date_format:d/m/Y|before:01/01/2000',
            'email'             => 'email|required',
            'skype'             => 'sometimes|',
            'mobile'            => 'digits_between:6,20|required',
            'phone'             => 'digits_between:6,20|required',
            'country'           => 'required|alpha_num',
            'province'          => 'required|alpha_num',
            'city'              => 'required|alpha_num',
            'address'           => 'required',
            'subagent'          => 'in:1,0',
            'comision'          => 'integer|max:100|min:1|required',
            'leader'            => 'required_if:subagent,1|exists:agente,id',
        ];
    }
}