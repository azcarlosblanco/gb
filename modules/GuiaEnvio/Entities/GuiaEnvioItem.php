<?php namespace Modules\GuiaEnvio\Entities;
   
use Illuminate\Database\Eloquent\Model;

class GuiaEnvioItem extends Model {

	protected $table="guia_remision_item";

    protected $fillable = ['description',
    						'num_copies',
    						'guia_remision'];

}