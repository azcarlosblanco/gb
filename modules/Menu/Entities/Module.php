<?php namespace Modules\Menu\Entities;
   
use Illuminate\Database\Eloquent\Model;

class Module extends Model {

	protected $table='module';
    protected $fillable = ['display_name',
    						'code',
    						'module_order',
    						'display'];

}