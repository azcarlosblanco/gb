<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditCardWayPay extends Model {

    protected $table = "credit_card_way_pay";
    protected $fillable = ["display_name"];
    use SoftDeletes;

}