<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payment\Entities\PolicyPaymentDetail;
use App\ProcedureDocument;

class CreditCardPaymentDetail extends Model implements PolicyPaymentDetail{

    protected $table = "credit_card_payment_detail";
    protected $fillable = [
    						"credit_card_type_id",
    						"credit_card_brand_id",
    						"credit_card_way_pay_id",
    						"policy_cost_id",
    						"card_num",
    						"expire_date",
                            "payment_date",
                            "value",
                            "state"
    						];
    use SoftDeletes;

    public function getProdceudreDocument(){
        $pd = ProcedureDocument::where("name","creditcard_auth_form")
                                    ->first();
        return $pd->id;
    }

    public function creditCardBrand(){
        return $this->belongsTo("Modules\Payment\Entities\CreditCardBrand",
                                "credit_card_brand_id");
    }

    public function creditCardType(){
        return $this->belongsTo("Modules\Payment\Entities\CreditCardType",
                                "credit_card_type_id");
    }

}