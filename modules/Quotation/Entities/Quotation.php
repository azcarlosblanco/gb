<?php namespace Modules\Quotation\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class quotation extends Model {

	protected $table = "quotation";
    protected $fillable = [
    						"date_quotation",
    						"agente_id",
    						"client_name",
    						"client_email",
    						"agent_id",
    						"obj_quotation"
    					  ];
    use SoftDeletes;

    public function agent(){
    	return $this->belongsTo("Modules\Agente\Entities\Agente");
    }
}