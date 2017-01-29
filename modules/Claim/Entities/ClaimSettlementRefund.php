<?php namespace Modules\Claim\Entities;

use Illuminate\Database\Eloquent\Model;
use JWTAuth;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClaimSettlementRefund extends Model {

  protected $table = 'claim_settlement_refund';
  protected $fillable = [
    'value',
    'payment_method_id',
    'claim_settlement_id',
    'to_supplier',
    'pay_date',
    'reference_number'
  ];

  use SoftDeletes;

  public function claimSettlement(){
    return $this->belongsTo('Modules\Claim\Entities\ClaimSettlement','claim_settlement_id','id');
  }

  public function paymentMethod(){
    return $this->belongsTo('Modules\Payment\Entities\PaymentMethod','payment_method_id','id');
  }

}
