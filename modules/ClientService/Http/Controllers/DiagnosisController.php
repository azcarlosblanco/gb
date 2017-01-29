<?php namespace Modules\Clientservice\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use App\Diagnosis;
use DB;
use Validator;

class DiagnosisController extends NovaController {
	
	public function store(Request $request)
	{
	  $input = $request->all();
      $result = new Diagnosis();
      $code = null;
      \DB::beginTransaction();
      
      try
        {
          $rules = array(
          "name"            => "required",
          "display_name"    => "required",
          );

          $vresult = Validator::make($input,$rules,array());
          if($vresult->fails()){
            $code=422;
            $this->novaMessage->setData($vresult->errors());
            throw new \Exception("Error Processing Request");
          }

          $result = new Diagnosis();
          $result->name             = $input['name'];
          $result->display_name     = $input['display_name'];
          $result->save();
          $code = 200;
          $this->novaMessage->setData(["id"=>$result->id]);

          $this->novaMessage->addSuccesMessage('Creado', 'Diagnostico Creado');
          \DB::commit();
        }catch(\Exception $e){
        	if($code == null){
        		$code = 500;
        	}
        	\DB::rollBack();
        	$this->novaMessage->addErrorMessage('Error', $e->getMessage());
        }
        return $this->returnJSONMessage($code);
    }
	
}