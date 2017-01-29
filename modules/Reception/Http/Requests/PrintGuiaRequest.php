<?php
namespace Modules\Reception\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Request;

class PrintGuiaRequest extends Request
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
            'receiver_name'       => 'required',
            'receiver_address'    => 'required',
            'carrier_id'          => 'required|exists:carrier,id',
            'filefields'        => 'required'
        ];
    }
}