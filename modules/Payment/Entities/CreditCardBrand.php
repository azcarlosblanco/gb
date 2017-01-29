<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CreditCardBrand extends Model {

	protected $table = "credit_card_brand";
    protected $fillable = ["display_name"];
    use SoftDeletes;

}