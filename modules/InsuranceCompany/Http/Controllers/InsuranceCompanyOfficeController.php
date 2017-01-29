<?php namespace Modules\InsuranceCompany\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Modules\InsuranceCompany\Entities\InsuranceCompany;
use Modules\InsuranceCompany\Entities\InsuranceCompanyEmail;
use Modules\InsuranceCompany\Entities\InsuranceCompanyOffice;
use Modules\InsuranceCompany\Entities\InsuranceCompanyOfficePhone;
use Illuminate\Http\Request;
use Modules\InsuranceCompany\Http\Requests\CreateInsuranceCompanyOfficeRequest;
use Modules\InsuranceCompany\Http\Requests\UpdateInsuranceCompanyOfficeRequest;
use JWTAuth;

class InsuranceCompanyOfficeController extends Controller {
    
    /**
     * Function Model: createOffice
     * Description: returns a view where the user can create an office for new insurance company
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @param [int] $[idOffice] [id insurance company to which the office belongs]
     * @return view
     */
	public function create($id)
	{
        $iCompany = InsuranceCompany::findOrFail($id);
        $company_office = new InsuranceCompanyOffice();
        $edit=false;
        $disabled="";
        return view('insurancecompany::create_office',compact('id','iCompany','company_office','edit','disabled'));
    }

    /**
     * Function Model: storeOffice
     * Description: create in the database an office to the insurance company identified by the id passed as paremeter
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @param [int] $[idOffice] [id insurance company to which the office belongs]
     * @return view
     */
	public function store($id, CreateInsuranceCompanyOfficeRequest $request)
	{
    	$input = $request->all();

    	$insurancecompany="";

    	\DB::beginTransaction();

    	try {
            $iCompany = InsuranceCompany::findOrFail($id);

    		$insuranceCompanyOffice = $iCompany->offices()->create([
	            'office_name' => $input['office_name'],
	            'representative' => $input['representative'],
	            'country' => $input['country'],
	            'state' => $input['state'],
	            'city' => $input['city'],
	            'address' => $input['address'],
	            'email' => $input['email']
	        ]);

            if($request->has('phone')){
                $phone=$insuranceCompanyOffice->phones()->create([
                    'number' => $input['phone'],
                    'default' => true,
                ]);
            }

    		\DB::commit();

            if($request->has('managaEmails')){
                //if this flag is active we want to create a office for the insurance company
                return redirect()->route('insurance_company_emails_view',['id'=>$id]);
            } 
    	}catch(\Exception $e){
            dd($e);
    		\DB::rollback();
    		//show message error
            return redirect('insurancecompany')->with('message_error', 'ompany was created successfully, Office could not be created');  
    	}
    	return redirect('insurancecompany')->with('message_error','Company was created successfully');
    }

    /**
     * Function Model: storeOffice
     * Description: create in the database an office to the insurance company identified by the id passed as paremeter
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @param [int] $[idOffice] [id insurance company to which the office belongs]
     * @return view
     */  
    public function view($id, $id_office){
        $edit=true;

        $iCompany = InsuranceCompany::findOrFail($id);

        $company_office = InsuranceCompanyOffice::with('phones')->findOrFail($id_office);


        $disabled="disabled";
        $user = JWTAuth::parseToken()->authenticate();
        if($user->can('insuranceCompany_editOffice')){
            $disabled="";
        }
        return view('insurancecompany::create_office',compact('iCompany','disabled','company_office','edit'));
    }

    /**
     * Function Model: update
     * Description: return a view with the info of the office
     * Date created: 21-04-2016
     * Cretated by : Rocio Mera S
     *               [rmera][@][novatechnology.com.ec]
     * I/O Specifications:
     * @param [int] $[idOffice] [id insurance company to which the office belongs]
     * @return view
     */    
    public function update($id, $id_office, UpdateInsuranceCompanyOfficeRequest $request){
        
        \DB::beginTransaction();

        $input = $request->all();

        try {
            $company_office=InsuranceCompanyOffice::findOrFail($id_office);

            $company_office->office_name=$input['office_name'];
            $company_office->representative=$input['representative'];
            $company_office->country=$input['country'];
            $company_office->state=$input['state'];
            $company_office->city=$input['city'];
            $company_office->address=$input['address'];
            $company_office->email=$input['email'];

            $company_office->update();

            $company_office->phones()->delete();

            if($request->has('phone')){
                $company_office->phones()->create([
                    'number' => $input['phone'],
                    'default' => true,
                ]);
            }

            \DB::commit();
        }catch(\Exception $e){
            \DB::rollback();
            dd($e);
            //show message error
            return redirect('insurancecompany')->with('message_error','Company was created successfully, Office could not be created');
        }

        return redirect('insurancecompany');
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
    public function delete($id, $id_office, Request $request)
    {
        \DB::beginTransaction();

        try {
            $office = InsuranceCompanyOffice::findOrFail($id_office);
            $office->delete();
            $message = $office->office_name." was deleted successfully";
            \DB::commit();
        }catch(\Exception $e){
            \DB::rollback();
            dd($e);
            $message = $office->company_name." could not be deleted";
        }
        
        if($request->ajax()){
            return $message;
        }
        return redirect('insurancecompany');  
    }



}