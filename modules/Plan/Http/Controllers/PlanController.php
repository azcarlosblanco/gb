<?php namespace Modules\Plan\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\Plan\Entities\Plan;
use Modules\Plan\Entities\Deducible;
use Modules\Plan\Entities\DeducibleOptions;
use Modules\Plan\Entities\PlanCost;
use Modules\Plan\Entities\NumberPayments;
use App\Http\Controllers\Nova\NovaController;
use Illuminate\Http\Request;
use App\NovaMessage;
use DB;
use JWTAuth;


class PlanController extends NovaController{
	
  public $novaMessage;

  function __construct()
  {
        //parent::__construct();
    $this->novaMessage=new NovaMessage();
  }
	//
    public function index($id=null)
    {
      	$insuranceCompany = InsuranceCompany::pluck('company_name','id');
        $planSelect=$deducibles=$numberPayments=collect([]);
        $plan=null;
        /*if($request->has('insurance_company_id')){
            $planSelect = Plan::where('insurance_company_id', $insuranceCompanyID)->pluck('name','id');
        }*/

        if(isset($id)){
            $deducibles=Deducible::with('deducibleOptions')
                                    ->where('plan_id',$id)
                                    ->orderBy('plan_deducible.id');

            $numberPayments=NumberPayments::all()->pluck('name','id');
      		  $plan=Plan::findOrFail($id)
                            ->with('planCosts')
                            ->orderBy('plan_cost.start_age')
                            ->orderBy('plan_cost.plan_deducible_id')
                            ->orderBy('plan_cost.number_payments_id');
            dd($plan);

      	}
      	return view('plan::index',compact(
                                    'insuranceCompany',
                                    'planSelect',
                                    'deducibles',
                                    'numberPayments',
                                    'plan'));
    }

	//ajax request
    public function getPlansByInsuranceCompany($insuranceCompanyID){
        try{
      		  $plansSelect=Plan::where('insurance_company_id', $insuranceCompanyID)
      					->pluck('name','id');
    		
        		if($request->ajax()){
                return response(json_encode($plansSelect))
                    		->header('Content-Type', 'application/json');
            }
        }catch(ModelNotFoundException $ex){
          	if($request->ajax()){
              	return response('Not Found', 404)
                      		->header('Content-Type', 'application/json');
            }
        }
    }

  	public function create()
  	{
    		//get list of insurance companies
    		$insuranceCompany = InsuranceCompany::pluck('company_name','id');
        $plan=null;
        $edit=false;
        $disabled="";
    		return view('plan::create_plan',compact(
                              'insuranceCompany',
                              'disabled',
                              'edit',
                              'plan')
                      );
  	}

	public function store(Request $request)
	{
		  $input=$request->all();	
    	try {
    		//create the agent
       		\DB::beginTransaction();
       		$plan = Plan::create($input);
       		/*if($request->has('createDeducibles')){
       		   return redirect('plan_create_deducible')->with('id'=>$plan->id);
       		}*/
    		  \DB::commit();
    	}catch(\Exception $e){
      		\DB::rollback();
      		//show message error
      		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
    	}
    	$this->novaMessage->addSuccesMessage('Created','Plan was created successfully');
    	return redirect('plan')->with('novaMessage',$this->novaMessage);
	}

	public function view($id)
	{
		  try{
          $plan=Plan::findOrFail($id);
          $edit=true;
	        $disabled="disabled";
          $user = JWTAuth::parseToken()->authenticate();
	        if($user->can('plan_edit')){
	            $disabled="";
	        }
	        //get lista de agentes
	        $insuranceCompany = InsuranceCompany::all()->pluck('company_name','id');
          return view('plan::create_plan',compact(
                                            'plan',
                                            'edit',
                                            'disabled',
                                            'insuranceCompany')
                                          );
      }catch(ModelNotFoundException $ex){
        	$this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
        	return redirect('agente')->with('novaMessage',$this->novaMessage);
      }
	}

	public function update($id)
	{
		  $input=$request->all();	
    	try {
    		//create the agent
       		\DB::beginTransaction();
       		$plan = Plan::findOrFail($id);
       		$plan->name=$input['name'];
       		$plan->description=$input['description'];
       		$plan->save();
    		  \DB::commit();
    	}catch(\Exception $e){
    		  \DB::rollback();
    		  //show message error
    	 	 $this->novaMessage->addErrorMessage('Error',$e->getMessage());
    	}
    	$this->novaMessage->addSuccesMessage('Updated','Plan was updated successfully');
    	return redirect('plan')->with('novaMessage',$this->novaMessage);
	}

	public function delete($id)
	{
		  $input=$request->all();	
    	try {
    		//create the agent
       		\DB::beginTransaction();
       		$plan = Plan::findOrFail($id);
       		$plan->delete();
    		  \DB::commit();
    	}catch(\Exception $e){
      		\DB::rollback();
      		//show message error
      		$this->novaMessage->addErrorMessage('Error',$e->getMessage());
    	}

    	if($request->ajax()){
          return $this->novaMessage->toJSON();
      }

    	$this->novaMessage->addSuccesMessage('Deleted','Plan was deleted successfully');
    	return redirect('plan')->with('novaMessage',$this->novaMessage);
	}

  
	
  public function getplaninfo(){
    $code=200;
    try{

        $result=plan::with('insuranceCompany')
                         ->with('deducibles')
                         ->get();    
        $data=array();
        $index=0;
        foreach ($result as $key => $value) {
          $data[$index]['description']    = $value['description'];
          $data[$index]['company_name']   = $value['insuranceCompany']['company_name'];
          $options       = array(); 

          foreach ($value['deducibles'] as $key => $deducible) {
            $options[] =  $deducible['name'];            
          }
           $data[$index]['opt_name'] = implode(",",$options);
          $data[$index]['buttons'] = array(
                                                array(
                                                    'class'  => 'available',
                                                    'active' => true,
                                                    'icon'   => 'glyphicon glyphicon-eye-open',
                                               'description' => 'Ver detalle',
                                                    'params' => [
                                                                  'id' => $value->id,
                                                                ],
                                                      'link' =>'.view'
                                                    )
                                             );
         
          $index++;
        }
          
        $this->novaMessage->setData($this->renderIndex($data));
      
    }catch(\Exception $e){
      //show message error
      $code=500;
        $this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
    }   
    return $this->returnJSONMessage($code);        
    }

  private function renderIndex($data){
        $index['display']['title']='Info. Planes';
        $index['display']['header']=array(
                            array('label' =>'Nombre',
                                  'filterType'=>'text',
                                  'fieldName' =>'description'),
                            array('label' =>'Compania',
                                'filterType'=>'text',
                                  'fieldName' =>'company_name'),
                            array('label' =>'Opciones',
                                'filterType'=>'text',
                                  'fieldName' =>'opt_name'),
                            /*array('label' =>'Botones de Accion',
                                'filterType'=>'text',
                                  'fieldName' =>'buttons')*/
                            );
        $index['list']=$data;
        return $index;
    }
    public function getdetail($id){
      $code=200;
      try{

          $result= DB::select('select c.value as costo, p.value as deducible from plan_cost c, plan_deducible_options p where p.plan_deducible_id=? and p.plan_deducible_id=c.plan_deducible_id',[$id]);
            
          $data=array();
          $index=0;
          //var_dump($result);
          foreach ($result as $value) {
            $data[$index]['values_cost']   = $value->costo;
            $data[$index]['values_ded']    = $value->deducible;
            $index++;
          }

          $this->novaMessage->setData($this->renderDetail($data));
          
      }catch(\Exception $e){
        //show message error
        $code=500;
          $this->novaMessage->addErrorMessage('Error getting data',$e->getMessage());
      }   
      return $this->returnJSONMessage($code);        
    }
   
  private function renderDetail($data){
        $index['display']['title']='Detalle de Planes';
        $index['display']['header']=array(
                            array('label'     =>'Valor Costo',
                                  'filterType'=>'text',
                                  'fieldName' =>'values_cost'),
                            array('label'     =>'Valor Option',
                                'filterType'  =>'text',
                                  'fieldName' =>'values_ded'),
                            );
        $index['list']=$data;

        
        return $index;
    }
}