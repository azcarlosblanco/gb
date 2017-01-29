<?php
namespace Modules\Policy\Http\Requests;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Requests\Request;
use Modules\Policy\Entities\Policy;

class CreatePolicyRequest extends Request
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
            
        ];
    }
}