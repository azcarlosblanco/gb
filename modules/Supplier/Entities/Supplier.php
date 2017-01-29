<?php namespace Modules\Supplier\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model {

    protected $table = 'supplier';
    protected $fillable = ['name', 'description', 'category'];
    use SoftDeletes;

    public function SupplierCategory(){
    	return $this->belongsTo('Modules\Supplier\Entities\SupplierCategory','category','id');
    }
}
