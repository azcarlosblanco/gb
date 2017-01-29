<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditCardType extends Model {

    protected $table = "credit_card_type";
    protected $fillable = ["display_name"];
    use SoftDeletes;

}