<?php
namespace Modules\Emission\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Request;

class RegisterCustomerPaymentRequest extends Request
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
            'date'   => 'required|before:tomorrow',
            'amount' => 'required|numeric',
            'payment_method_id' => 'required|exists:payment_method,id',
            'filefields'        => 'required',
        ];
    }
}