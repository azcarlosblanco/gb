<?php namespace Modules\GuiaEnvio\Entities;
   
use Illuminate\Database\Eloquent\Model;

class GuiaEnvioRelation extends Model {

    protected $table="guia_remision_relation";

    protected $fillable = ['table_name',
    						'table_id',
    						'guia_remision_id'];


}