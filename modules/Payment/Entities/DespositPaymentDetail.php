<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payment\Entities\PolicyPaymentDetail;
use App\ProcedureDocument;

class DespositPaymentDetail extends Model implements PolicyPaymentDetail{

    protected $table = "deposit_payment_detail";
    protected $fillable = [
    						"policy_cost_id",
    						"desposit_num",
    						"bank_name",
    						"account_num",
    						"payment_date",
    						"value",
                            "state"
    						];
    use SoftDeletes;

    public function getProdceudreDocument(){
        $pd = ProcedureDocument::where("name","payment_proof")
                                    ->first();
        return $pd->id;
    }

}