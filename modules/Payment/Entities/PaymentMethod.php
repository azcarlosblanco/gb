<?php namespace Modules\Payment\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentMethod extends Model {

	protected $table='payment_method';
    protected $fillable = ['method',
    						'display'];
    use SoftDeletes;

}