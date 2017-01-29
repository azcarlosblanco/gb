<?php namespace Modules\Reception\Entities;

use App\ProcessEntry;
use Modules\Costumer\Entities\Customer;
use Modules\Policy\Entities\Policy;
use App\UploadAndDownloadFile;
use Illuminate\Http\Request;
use App\User;
use JWTAuth;


/**
 * Class: ProcedureType
 * Description: This class represent the types of procedure that can be in the system
 * Date created: 03-05-2016
 * Cretated by : Rocio Mera S
 *               [rociom][@][novatechnology.com.ec]
 */
class ProcessClaimsInit extends ProcessEntry
{
	use \App\UploadAndDownloadFile;

	function __construct(){
		//call to method start of the
		parent::__construct(array(),'ClaimsInit');
	}

	public function getResponsibleID($current=true){
		//the responsible is the user that is currently logged in the application
		return parent::getResponsibleID($current);
	}

	public static function findAndFinish($procedure_id){
		try{
			\DB::beginTransaction();
			$procedure = \App\ProcedureEntry::findOrFail($procedure_id);
			$process = self::findProcess(
									$procedure->getLastActiveProcess()->id
								);
			$process->finish();
			\DB::commit();
			return true;
		}catch( \Exception $e ){
			\DB::rollback();
			return false;
		}
	}//end findAndFinish
}
