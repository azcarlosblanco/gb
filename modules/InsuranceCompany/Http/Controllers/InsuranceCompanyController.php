<?php namespace Modules\InsuranceCompany\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\InsuranceCompany\Entities\InsuranceCompanyEmail;
use Modules\InsuranceCompany\Entities\InsuranceCompanyOffice;
use Modules\InsuranceCompany\Entities\InsuranceCompanyPhone;
use Illuminate\Http\Request;
use Modules\InsuranceCompany\Http\Requests\CreateInsuranceCompanyRequest;
use Modules\InsuranceCompany\Http\Requests\UpdateInsuranceCompanyRequest;
use Modules\InsuranceCompany\Http\Requests\CreateInsuranceCompanyOfficeRequest;
use Modules\InsuranceCompany\Http\Requests\UpdateInsuranceCompanyOfficeRequest;
use JWTAuth;

class InsuranceCompanyController extends Controller {
	
	/**
     * Function Model: index
     * Description: returns a paginate list of insurance companies
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return view
     */
	public function index()
	{
		$insurancecompanies = InsuranceCompany::with('offices')->orderBy('company_name')->paginate(5);
		/*$insurancecompanies = InsuranceCompany::with(['offices' => function ($query) {
		    $query->select('insurance_company_office.id','insurance_company_office.office_name');
		}])->orderBy('company_name')->paginate(5);
		dd($insurancecompanies);*/
		return view('insurancecompany::index',compact('insurancecompanies'));
	}
	
	/**
     * Function Model: create
     * Description: returns a view where the user can create a new insurance company
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @return view
     */
	public function create()
	{
        $iCompany=null;
        $edit=false;
        $disabled="";
        return view('insurancecompany::create',compact('iCompany','edit','disabled'));
    }

    /**
     * Function Model: store
     * Description: create a register with a new insurance company
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @param [CreateUserRequest] $[request] [<description>]
     * @return view
     */
    public function store(CreateInsuranceCompanyRequest $request)
    {
    	$input = $request->all();

    	$insurancecompany="";

    	$message_error = "";
    	
    	\DB::beginTransaction();

    	try {
    		$insurancecompany = InsuranceCompany::create([
	            'company_name' => $input['company_name'],
	            'representative' => $input['representative']
	        ]);
    		\DB::commit();

	        if($request->has('createOffice')){
	    		//if this flag is active we want to create a office for the insurance company
	    		return redirect()->route('insurance_company_office_create',['id'=>$insurancecompany->id]);
	    	}
    	}catch(\Exception $e){
    		\DB::rollback();
    		//show message error
            echo($e->getMessage());   //
    		return redirect('insurancecompany')->with('message_error',$e->getMessage());
    	}
    	return redirect('insurancecompany')->with('message_error','Company was created successfully');
    }

    /**
     * Function Model: view
     * Description: return a view with teh information of teh insurance offfice
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @param [CreateUserRequest] $[request] [<description>]
     * @return view
     */
    public function view($id)
    {
    	$iCompany = InsuranceCompany::findOrFail($id);
        $edit=true;
        $disabled="disabled";
        $user = JWTAuth::parseToken()->authenticate();
        if($user->can('insuranceCompany_edit')){
            $disabled="";
        }
        return view('insurancecompany::create',compact('iCompany','disabled','edit'));
    }

    /**
     * Actualiza info de un usuario
     * @param  int            $id      Id del usuario
     * @param  UpdateUserRequest $request Reglas de validación
     * @return redirect                     
     */
    public function update($id, UpdateInsuranceCompanyRequest $request)
    {
        \DB::beginTransaction();

        try {
            $insurancecompany = InsuranceCompany::findOrFail($id);
            $insurancecompany->company_name = $request['company_name'];
            $insurancecompany->representative = $request['representative'];
            $insurancecompany->update();
            \DB::commit();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            return redirect('insurancecompany')->with('message_error',$e->getMessage());
        }
        
        return redirect('insurancecompany')->with('message_error','company was created successfully');;
    }

    /**
     * Elimina una compañia de seguros         
     * Date created: 26-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @param  int $id Id de la compañia que vamos a eliminar
     * @return redirect   
     * */
    public function delete($id, Request $request)
    {
     	\DB::beginTransaction();

    	try {
    		$iCompany = InsuranceCompany::findOrFail($id);
    		$iCompany->delete();
    		$message = $iCompany->company_name." was deleted successfully";
    		\DB::commit();
    	}catch(\Exception $e){
    		\DB::rollback();
    		$message = $iCompany->company_name." could not be deleted";
    	}
    	
        if($request->ajax()){
        	return $message;
        }
        return redirect('insurancecompany');  
    }

    public function viewEmails($id){
    	$iCompany = InsuranceCompany::with('emails')->findOrFail($id);

        $email_reason = array("emisiones"=>"emisiones","renovaciones"=>"renovaciones","actualizar info"=>"actualizar info","general"=>"general");

        $disabled="disabled";
        $user = JWTAuth::parseToken()->authenticate();
        if($user->can('insuranceCompany_manageEmails')){
            $disabled="";
        }
    	return view('insurancecompany::create_emails',compact('iCompany','disabled','email_reason'));
    }

    /**
     * Administra los emails usado para hacer los tramites en la compañia
     * @param  int $id Id del usuario
     * @return redirect            
     */
    public function manageEmails($id, Request $request)
    {
    	\DB::beginTransaction();

        $input = $request->all();

    	try {
    		$iCompany = InsuranceCompany::findOrFail($id);
    		
            $iCompany->emails()->delete();

            $emailsCompany=$request->get('iEmail');
            foreach ($emailsCompany as $key => $company) {
                $iCompany->emails()->create([
                    'email' => $company['email'],
                    'contact_name' => $company['contact_name'],
                    'reason' => $company['reason'],
                ]);
            }

    		$message = $iCompany->company_name." emails were edited successfully";
    		\DB::commit();
    	}catch(\Exception $e){
    		\DB::rollback();
            dd($e);
    		$message = $iCompany->company_name." emails could not be changed";
            echo($message);
    	}

        /*if($request->ajax()){
        	return $message;
        }*/
        
        return redirect('insurancecompany');   
    }
	
}