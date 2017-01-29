<?php namespace Modules\Claim\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Claim\Entities\ClaimConcept;
use DB;
use Validator;

class ClaimConceptController extends NovaController {

	public function store(Request $request)
	{
		$input= $request->all();
		$result = new ClaimConcept();
	    $code=null;
	    \DB::beginTransaction();
	    try{
	    	$rules = array(
					"name"            => "required",
					"display_name"    => "required",
					"notify"          => "required",
					"deduct_discount" => "required|numeric"
				);

    		$vresult = Validator::make($input,$rules,array());
    		if($vresult->fails()){
    			$code=422;
    			$this->novaMessage->setData($vresult->errors());
    			throw new \Exception("Error Processing Request");
    		}

	      $result = new ClaimConcept();
	      $result->name              = $input['name'];
	      $result->display_name      = $input['display_name'];
	      $result->notify            = $input['notify'];
	      $result->deduct_discount   = $input['deduct_discount'];
	      $result->save();
	      $code=200;

	      $this->novaMessage->addSuccesMessage('Creado','Concepto creado');
	      \DB::commit();
	    }catch(Exception $e){
	    	if($code==null){
	    		$code=500;
	    	}
	       \DB::rollBack();
	       $this->novaMessage->addErrorMessage('Error', $e->getMessage());
	    }
	    return $this->returnJSONMessage($code);
	}
	
}