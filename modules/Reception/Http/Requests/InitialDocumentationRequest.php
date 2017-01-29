<?php
namespace Modules\Reception\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Request;

class InitialDocumentationRequest extends Request
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
            'name'              => 'required',
            'lastname'          => 'required',
            'identity_document' => 'required',
            'phone'             => 'digits_between:6,20',
            'mobile'            => 'digits_between:6,20',
            'email'             => 'email',
            'agente_id'         => 'required|exists:agente,id',
            'plan_id'           => 'required|exists:plan,id'
        ];
    }
}