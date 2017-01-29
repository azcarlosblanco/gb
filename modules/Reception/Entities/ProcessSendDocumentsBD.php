<?php namespace Modules\Reception\Entities;

use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\User;
use Modules\Agente\Entities\Agente;
use Modules\Plan\Entities\Plan;
use App\ProcessEntry;
use App\ProcedureEntry;
use App\ProcessCatalog;
use Modules\GuiaEnvio\Entities\GuiaEnvio;
use Modules\GuiaEnvio\Entities\GuiaEnvioItem;
use Modules\GuiaEnvio\Entities\SendDocument;
use Modules\GuiaEnvio\Entities\SendDocumentItem;
use JWTAuth;


class ProcessSendDocumentsBD extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'SendDocumentsBD');
	}

	public function doProcess(Request $request,$dataGuia){
		$policy=$this->procedureEntryRel->policy;
		$policy->state='wait_receive_bd';
		$policy->save();

		$data['date']=$dataGuia['date'];
        $data['track_number']=$dataGuia['track_number'];
        $data['reason']=$dataGuia['reason'];
        $data['sender']=$dataGuia['sender'];
        $data['receiver_name']=$dataGuia['receiver_name'];
        $data['receiver_address']=$dataGuia['receiver_address'];
        $data['receiver_phone']=$dataGuia['receiver_phone'];
        $data['carrier_id']=$dataGuia['carrier_id'];
        $data['external_track_number']=$dataGuia['external_track_number'];
        $data['foreign_id']=$dataGuia['foreign_id'];

        $dataItem=$dataGuia['guia_items'];

        //mark document as sent
        $doc=SendDocument::where('process_id',$this->id)
                    		->first();
        $doc->state='sent';
        $doc->save();

		//guardar guia en la base
		$guia=GuiaEnvio::create(
				$data
			);

		//guardar items de la guia en la base
		$items=array();
		foreach ($dataItem as $key => $item) {
			$items[]=['description'=>$item['description'],
						'num_copies' =>$item['num_copies'],
						'guia_remision_id'=> $guia->id];
		}

		GuiaEnvioItem::insert($items);

		//upload guia
		$params['fieldname']='filefields';
		$params['table_type']='procedure_entry';
		$params['table_id']=$this->procedure_entry_id;
		$params['subfolder']='newPolicy/'.$this->procedure_entry_id;
		$params['multiple']=true;
		$params['description_files'][]='guia_remision_to_db';
		$entryIDs=$result=$this->uploadFiles($request,$params);

		$guia->file_entry_id=$entryIDs[0];
		$guia->save();
	}


	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}
}