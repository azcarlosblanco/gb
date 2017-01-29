<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeHospitalization extends Model {

    
    protected $table = "type_hospitalizations";
    protected $fillable = ['name'];

    use SoftDeletes;

}