<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BankAccountType extends Model {

	protected $table = "bank_account_type";
    protected $fillable = ["display_name"];
    use SoftDeletes;

}