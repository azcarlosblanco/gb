<?php namespace Modules\Emission\Entities;

use App\ProcessEntry;
use App\FileEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use Modules\Email\Entities\EmailUtils;
use Modules\Agente\Entities\Agente;
use Modules\Payment\Entities\Payment;
use Modules\Plan\Entities\Deducible;
use Modules\InsuranceCompany\Entities\InsuranceCompanyEmail;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;
use Modules\Authorization\Entities\Role;
use Modules\Affiliate\Entities\AffiliatePolicyAnnex;
use Modules\Affiliate\Entities\AffiliatePolicyExtra;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessReviewProspectPolicy extends ProcessEntry
{
	use \App\UploadAndDownloadFile;
	private $email_template_reason
					='sendProspectPolicyAgent';

	function __construct(){
		//call to method start of the 
		parent::__construct(array(),'ReviewProspectPolicy');
	}

	public function getEmailTemplateReason(){
		return $this->email_template_reason;
	}

	public function doProcess($policy){
		//send email to the agent with the information of the policy
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID(true);
	}
}