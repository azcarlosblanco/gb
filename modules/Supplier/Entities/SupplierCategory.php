<?php namespace Modules\Supplier\Entities;

use Illuminate\Database\Eloquent\Model;

class SupplierCategory extends Model {

    protected $table = 'supplier_category';
    protected $fillable = ['name'];


    
    public function Supplier(){
    	return $this->hasMany('Modules\Supplier\Entities\Supplier','category','id');
    }
}

