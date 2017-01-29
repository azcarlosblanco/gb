<?php namespace Modules\Renovation\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\ProcedureEntry;
use App\Role;
use App\Http\Controllers\Nova\NovaController;
use Modules\Agente\Entities\Agente;

class RenovationController extends NovaController {

    // protected $module_path='renovations';

	function __construct()
	{
        parent::__construct();
	}

	public function index(Request $request)
	{
        try{
            $content=[];
            $user = \JWTAuth::parseToken()->authenticate();
            $roles_user=$user->roles->lists('name');
            $manager=false;
            foreach ($roles_user as $value) {
                if(in_array($value,['administracion','emision_manager','recepcion_manager'])){
                    $manager=true;
                }
            }

            $userID=($manager)?"":$user->id;

            $procedure_name='newpolicy';
            /*****type=emissions****/
            $type="Emisiones";
            if($request['type']=='claim'){
                $procedure_name='claims';
                $type="Reclamos";
            }else if($request['type']=='settlement'){
                $procedure_name='settlement';
                $type="Liquidaciones";
            }

            $listProcedures=
                ProcedureEntry::getListPendingProcedure(
                                $procedure_name,
                                $userID);

            $actionButtons=[];
            $result=[];
            $affliates=array();
            foreach ($listProcedures as $item) {
                if(isset($result[$item->id])) continue;

                $procedure=ProcedureEntry::find($item->id);
                if(empty($actionButtons)){
                    $role= Role::where('name','recepcion')->first();
                    $actionButtons=$procedure->getListActionButtons($role->id);
                }
                $buttons=$procedure
                            ->getListButtons($actionButtons,
                                            $item->current_process_id,
                                            $item->process_catalog_id);
                if(isset($buttons['current_description'])){

                    //Get information policy and client
                    if(is_null($procedure->policy_id)){
                        $data=self::getRequestPolicyData($item);
                        $result[$item->id]['policy_number']="";
                        $result[$item->id]['client']=$data['customer_fullname'];
                        $agente=Agente::find($data['agente_id']);
                        $result[$item->id]['agent']=$agente->getFullNameAttribute();
                    }else{
                        $result[$item->id]['policy_number']=$procedure->policy->policy_number;
                        if($procedure_name=='settlement'){
                            //obtener el nombre del afiliado
                            $affiliate=$procedure->claims()
                                        ->with('affiliatePolicy')
                                        ->first()
                                        ->affiliatePolicy
                                        ->affiliate;
                            $result[$item->id]['affiliate']=$affiliate->getFullNameAttribute();
                        }
                        $result[$item->id]['client']=$procedure
                                                        ->policy
                                                        ->customer
                                                        ->getFullNameAttribute();


                        $agente=Agente::find($procedure->policy->agente_id);
                        $result[$item->id]['agent']=$agente->getFullNameAttribute();
                    }
                    $result[$item->id]['procedure_id']=$procedure->id;
                    $result[$item->id]['state']=$buttons['current_description'];
                    $result[$item->id]['pcd_start_date']=date('m-d-Y',strtotime($item->pcd_start_date));
                    $result[$item->id]['prs_start_date']=date('m-d-Y',strtotime($item->prs_start_date));
                    $result[$item->id]['buttons']=$buttons['buttons'];

                    //add button to cancel the procedure
                    /*$result[$item->id]['buttons'][] = array(
                                                         'class' => 'delete',
                                                         'params' => [
                                                             'id' => $item->id,
                                                             'message' => 'Confirma que desea dar de baja el trámite. Esta acción no puede ser revertida',
                                                             'method' => 'DELETE',
                                                             'procedure_name' => $procedure_name,
                                                             'uri' =>
                                                                'root.seguros.cancel-procedure',
                                                         ]
                                                    );*/

                    $result[$item->id]['operator']=$item->u_name." ".$item->u_lastname;
                    $result[$item->id]['searchingField']=strtoupper(
                                        $result[$item->id]['policy_number'].
                                        $result[$item->id]['client'].
                                        $result[$item->id]['procedure_id'].
                                        $result[$item->id]['state'].
                                        $result[$item->id]['operator']
                                                );
                }
            }
            //search
            $search="";
            if($request->has('search_data')){
                $search=strtoupper($request['search_data']);
            }
            $list=array();
            foreach ($result as $values) {
                if ($search==="" || strpos($values['searchingField'], $search) !== false)
                    $list[]=$values;
            }
            if($request->has('withView')){
                $this->novaMessage->setData($this->renderIndex($list,$type));
            }else{
                $this->novaMessage->setData($list);
            }
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            $this->novaMessage->addErrorMessage("Error",$e->getMessage());
            return $this->returnJSONMessage(500);
        }
	}

    public function addRenovations(Request $request)
    {
        return \Response::json(['hola' => 'hola']);
    }

    public function uploadRenovation(Request $request)
    {
        if ($request->file('file')) {

          $file = $request->file('file');

          if ($file->getClientOriginalExtension() == 'xlsx' || $file->getClientOriginalExtension() == 'xls') {



              $results = \Excel::load($file)->get();

              dd($results[0]['policy_number']);

          }

          $data = [
            'success' => false,
            'content' => 'Extensíon incorrecta'
          ];

        }

        $data = [
          'success' => false,
          'content' => 'Falta el archívo'
        ];

    }

    private function renderIndex($content,$type){
        $index['display']['title']="Recepción - $type Pendientes";
        $index['display']['header'][]=array('label' =>'Trámite',
                                          'filterType'=>'text',
                                          'fieldName' =>'procedure_id');
        $index['display']['header'][]=array('label' =>'Póliza',
                                          'filterType'=>'text',
                                          'fieldName' =>'policy_number');
        if($type=='Liquidaciones'){
            //afiliado
            $index['display']['header'][]=array('label' =>'Afiliado',
                                              'filterType'=>'text',
                                              'fieldName' =>'affiliate');
        }else{
            //cliente
            $index['display']['header'][]=array('label' =>'Cliente',
                                              'filterType'=>'text',
                                              'fieldName' =>'client');
        }
        $index['display']['header'][]=array('label' =>'Inicio Trámite',
                                              'filterType'=>'date',
                                              'fieldName' =>'pcd_start_date');
        $index['display']['header'][]=array('label' =>'Inicio Proceso',
                                              'filterType'=>'date',
                                              'fieldName' =>'prs_start_date');
        $index['display']['header'][]= array('label' =>'Estado',
                                              'filterType'=>'text',
                                              'fieldName' =>'state');
        $index['display']['header'][]= array('label' =>'Botenes de Acción',
                                               'fieldName' =>'buttons');
        $index['list']=$content;
        return $index;
    }

}
