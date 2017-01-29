<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Plan\Entities\NumberPayments;
use Modules\Affiliate\Entities\AffiliateRole;
use Modules\Payment\Entities\PaymentMethod;
use Modules\Payment\Entities\PolicyCostDetail;
use Modules\Payment\Entities\PolicyCostTaxFees;
use Modules\Payment\Entities\CreditCardPaymentDetail;
use Modules\Payment\Entities\TransferPaymentDetail;
use Modules\Payment\Entities\DespositPaymentDetail;
use Modules\Payment\Entities\ChequePaymentDetail;


class PolicyCost extends Model {

    const S_PAIDOFF = 0;
    const S_PENDING = 1;
    const S_CANCELLED = 2;

    protected $table='policy_cost';
    protected $fillable = [ 
    						'total',                //total pay
                            'state',                //0 paidoff, 1 pending , 2 cancelled
                            'policy_id',            //policy to which the pay correspond
                            'quote_number',         //this payment to which qouta correspond
                            'cancellation_reason',
                            'date_paidoff',
                            'emision_number',
                            'renewal_number',
                            'user_regiter_cost',
                            'custom_cost'
    					   ];
    use SoftDeletes;

    public function policy(){
        return $this->belongsTo("Modules\Policy\Entities\Policy",
                                    "policy_id");
    }

    public function policyCostDetails(){
        return $this->hasMany("Modules\Payment\Entities\PolicyCostDetail",
                                    "policy_cost_id");
    }

    public function chequePayment(){
        return $this->hasMany("Modules\Payment\Entities\ChequePaymentDetail",
                                    "policy_cost_id");
    }

    public function creditCardPayment(){
        return $this->hasMany("Modules\Payment\Entities\CreditCardPaymentDetail",
                                    "policy_cost_id");
    }

    public function depositPaymentPayment(){
        return $this->hasMany("Modules\Payment\Entities\DespositPaymentDetail",
                                    "policy_cost_id");
    }

    public function transferPayment(){
        return $this->hasMany("Modules\Payment\Entities\TransferPaymentDetail",
                                    "policy_cost_id");
    }

    //payment methods
    public function getPaymentMethods(){
        
        $paymentMethods = [
                            "cheque",
                            "transfer",
                            "creditcard",
                            "deposit"
                          ];

        $payments = array();
        foreach ($paymentMethods as $method) {
            $payments[$method] = array();
            switch ($method) {
                case 'cheque':
                    $payments[$method] = $this->chequePayment()->get();
                    break;
                case 'transfer':
                    $payments[$method] = $this->transferPayment()->get();
                    break;
                case 'creditcard':
                    $payments[$method] = $this->creditCardPayment()->get();
                    break;
                case 'deposit':
                    $payments[$method] = $this->depositPaymentPayment()->get();
                    break;
            }
        }

        return $payments;
    }


    /*
     * This function check if the paymente date for the different procedures is inside de limits
     * availables
     * @params: $procedure : name of the procedure
                $paymentDate: date in which the payment was made in format "Y-m-d"
     * return:  "cancel_policy" => when the date is outside the linits and the policy has to be cancelled
                "valid_date" => when the date is inside the limit
     */
    public function checkPayDateRestrictions($procedure,$paymentDate){
        
        if($procedure=="emission"){
            $startDate = $this->policy->start_date;
            //payment date
            $pd = Carbon::createFromFormat('Y-m-d', $paymentDate);
            //emission date
            $ed = Carbon::createFromFormat('Y-m-d', $startDate);

            if($pd->diffInDays($ed)>60){
                return "cancel_policy";
            }else{
                return "valid_date";
            }
        }else if($procedure=="renewal"){
            print("TODO");
        }
    }

    public static function stateQuoteArray(){
        return [self::S_PAIDOFF=>"Pagado",
                self::S_PENDING=>"Pendiente",
                self::S_CANCELLED=>"Cancelado"];
    }

    public function registerPayment($payment){
        $pm = PaymentMethod::find($payment["payment_method_id"]);

        if($pm==null){
            throw new \Exception("Invalid Pyament Method");
        }

        switch ($pm->method) {
            case 'creditcard':
                return $this->registerCreditCardPayment($payment);
                break;
            case 'transfer':
                return $this->registerTransferPayment($payment);
                break;
            case 'cheque':
                return $this->registerChequePayment($payment);
                break;
            case 'deposit':
                return $this->registerDepositPayment($payment);
                break;
            default:
                throw new \Exception("Invalid Pyament Method");
                break;
        }
    }

    public function registerCreditCardPayment(array $payment){
        $p = CreditCardPaymentDetail::create([
                                    "policy_cost_id"         => $this->id,
                                    "payment_date"           => $payment["payment_date"],
                                    "value"                  => $payment["value"],
                                    "credit_card_type_id"    => $payment["credit_card_type_id"],
                                    "credit_card_brand_id"   => $payment["credit_card_brand_id"],
                                    "credit_card_way_pay_id" => $payment["credit_card_way_pay_id"],
                                    "card_num"               => "",
                                    "expire_date"            => "",
                                    "state"                  => $payment["state"]
                                        ]);
        return $p;
    }

    public function registerTransferPayment(array $payment){
        $p = TransferPaymentDetail::create([
                                    "policy_cost_id"       => $this->id,
                                    "payment_date"         => $payment["payment_date"],
                                    "value"                => $payment["value"],
                                    "transfer_num"         => $payment["transfer_num"],
                                    "bank_name"            => $payment["bank_name"],
                                    "bank_account_type_id" => $payment["bank_account_type_id"],
                                    "titular_account"      => $payment["titular_account"],
                                    "account_num_from"     => $payment["account_num_from"],
                                    "state"                => $payment["state"]
                                        ]);
        return $p;
    }

    public function registerChequePayment(array $payment){
        $p = ChequePaymentDetail::create([
                                    "policy_cost_id"       => $this->id,
                                    "payment_date"         => $payment["payment_date"],
                                    "value"                => $payment["value"],
                                    "cheque_num"           => $payment["cheque_num"],
                                    "bank_name"            => $payment["bank_name"],
                                    "state"                => $payment["state"]
                                        ]);
        return $p;
    }

    public function registerDepositPayment(array $payment){
        $p = DespositPaymentDetail::create([
                                    "policy_cost_id"         => $this->id,
                                    "payment_date"           => $payment["payment_date"],
                                    "value"                  => $payment["value"],
                                    "desposit_num"           => $payment["desposit_num"],
                                    "bank_name"              => $payment["bank_name"],
                                    "account_num"            => $payment["account_num"],
                                    "state"                  => $payment["state"]
                                        ]);
        return $p;
    }

}