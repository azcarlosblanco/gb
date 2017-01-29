<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payment\Entities\PolicyPaymentDetail;
use App\ProcedureDocument;

class TransferPaymentDetail extends Model implements PolicyPaymentDetail{

    protected $table = "transfer_payment_detail";
    protected $fillable = [
    						"policy_cost_id",
    						"transfer_num",
    						"bank_name",
    						"bank_account_type_id",
    						"titular_account",
    						"account_num_from",
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