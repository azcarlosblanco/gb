<?php namespace Modules\Emission\Entities;

use App\ProcessEntry;
use App\ProcedureEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\User;
use JWTAuth;
use Modules\Authorization\Entities\Role;
use App\FileEntry;

/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessUploadPolicyRequest extends ProcessEntry
{
	public $email_template_reason='requestPolicyInsuranceCompany';

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'UploadPolicyRequest');
	}

	public function doProcess($data){
		
	}

	public function getResponsibleID($current=false){
		return parent::getResponsibleID($current);
	}

	public function getTemplateEmail($data){
		$template="Estimado,\r\n Favor procesar la solicitud adjunta, vigencia <EFFECTIVE_DATE>.\r\n";
		if($data['request_discount']){
			$template .="Por favor realizar un descuento del ".$data['per_discount']."% a la póliza por concepto de pago en ".$data["payment_method"]."\r\n";
		}
		if($data['request_invoice']){
			$template .="Por favor enviar junto con la póliza la factura del cliente";
		}

		return $template;
	}

	public function getFilesSendEmail(){
		$procedureId=$this->procedureEntryRel->id;

		$attachments = FileEntry::where('table_type','procedure_entry')
								->where('table_id',$procedureId)
								->select('complete_path as pathToFile',
									'mime',
									'original_filename as display',
									'filename',
									'id')
								->get()
								->toArray();
		return $attachments;
	}

}