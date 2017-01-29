<?php namespace Modules\Claim\Entities;

use Illuminate\Database\Eloquent\Model;
use JWTAuth;

class ClaimProcedure extends Model {

    protected $table = 'claim_procedure';
    protected $fillable = ['claim_id', 'procedure_entry_id'];

    public function claim(){
    	return $this->belongsTo('Modules\Claim\Entities\Claim','claim_id','id');
    }

    public function procedureEntry(){
    	return $this->belongsTo('App\ProcedureEntry','procedure_entry_id','id');
    }

}
