<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payment\Entities\PolicyPaymentDetail;
use App\ProcedureDocument;


class ChequePaymentDetail extends Model implements PolicyPaymentDetail {

	protected $table = "cheque_payment_detail";
    protected $fillable = [
    						"policy_cost_id",
    						"cheque_num",
    						"bank_name",
    						"payment_date",
    						"value",
    						"state"
    					];

    use SoftDeletes;

    public function getProdceudreDocument(){
        $pd = ProcedureDocument::where("name","paycheck")
                                    ->first();
        return $pd->id;
    }
}