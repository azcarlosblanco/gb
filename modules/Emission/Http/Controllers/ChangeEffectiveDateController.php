<?php namespace Modules\Emission\Http\Controllers;

use Pingpong\Modules\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Nova\NovaController;
use Modules\Agente\Entities\Agente;
use Modules\Emission\Entities\ProcessUploadPolicyRequest;
use Modules\Emission\Entities\ProcessChangeEffectiveDate;
use App\ProcessEntry;
use Modules\Emission\Http\Requests\ChangeEffectiveDateRequest;
use Carbon\Carbon;

class ChangeEffectiveDateController extends NovaController {
	
	function __construct()
	{
        parent::__construct();
	}

	public function form($process_ID, Request $request){
        try{
            $pup=ProcessChangeEffectiveDate::findProcess($process_ID);
            if($pup==null){
                throw new \Exception("Process with does id does not exist", 1);  
            }
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                throw new \Exception("Process is already complete", 1); 
            }
            $policy=$pup->procedureEntryRel->policy;
            $data['current_date']=date("m/d/Y", strtotime($policy->start_date));
            $data['start_date']=$policy->start_date;
            

            $this->novaMessage->setData(
                            $this->renderForm($data,$pup->id)
                        );
            return $this->returnJSONMessage();
        }catch(\Exception $e){
            \DB::rollback();
            //show message error
            $this->novaMessage->addErrorMessage('NOT FOUND',$e->getMessage());
            return $this->returnJSONMessage(404);
        }
	}

	public function change($process_ID,ChangeEffectiveDateRequest $request){
		$code = null;
        \DB::beginTransaction();
        try{
            $pup=ProcessChangeEffectiveDate::findProcess($process_ID);

            //check process if the same time that we are requeted
            if($pup==null){
                $code = 404;
                throw new \Exception("No existe proceso", 1);  
            }
            
            if($pup->state=='finished' || 
                $pup->state=='cancelled' || 
                $pup->state=='rellocated'){
                $code = 400;
                throw new \Exception("Process is already complete", 1); 
            }

            $pup->doProcess($request);
            $pup->finish();

            \DB::commit();
            $code = 200;
            $this->novaMessage->setRoute(
                    'emission/pending');
        }catch(\Exception $e){
            \DB::rollback();
            if($code == null){
                $code = 500;
            }
            //show message error
            $this->novaMessage->addErrorMessage('Error',$e->getMessage());
        }
        return $this->returnJSONMessage($code);
	}

    private function generateDates(){
        $carbon = new Carbon(); 
        $month=$carbon->month;
        $day=$carbon->day;
        $year=$carbon->year;
        
        $dates=array();
        if($day>1 && $day<=15){
            $dmont = $month;
            if($month<10){
                $dmont = "0".$month;
            }
            $dates[$year.'-'.$month.'-15']=$dmont.'/15/'.$year;
        }else{
            $firstday=1;
        }
        $month=$month+1;

        for($i=0;$i<8;$i++){
            $dmont = $month;
            if($month<10){
                $dmont = "0".$month;
            }

            $dates[$year.'-'.$month.'-1']=$dmont.'/01/'.$year;
            $dates[$year.'-'.$month.'-15']=$dmont.'/15/'.$year;
            if($month==12){
                $year=$year+1;
                $month=1;
            }
            $month++;
        }
        return $dates;
    }

    public function renderForm($data,$id){
        $form["url"]="emission/newPolicy/changeEffectiveDate/$id";
        $form["method"]="POST";
        $form["title"]="Emisión - Solicitar Póliza";
        $form['sections'][0]=[
                            'fields'=>array( 
                                    array(
                                            "label" =>"Fecha Inicio Cobertura",
                                            "name"=>"start_date",
                                            "type"=>"select",
                                            "options"=>$this->generateDates()
                                        ),
                                ),
                            ];
        $form['data_fields']=$data;
        $form['actions'][]=array(
                                'display' => 'Solicitar Póliza Best Doctors',
                                'type'    => 'submit'
                            );
        return $form;
    }

}