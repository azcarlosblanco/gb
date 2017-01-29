<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcedureCancellation extends Model
{
    protected $table='procedure_cancellation';
	
	protected $fillable=[
						'responsible_id', //who dis the action
						'reason', //comment why teh procedure was cancelled
						'procedure_entry_id' //id of procedure that was cancelled
						];

	//to enable soft delete in the model
    use SoftDeletes;

    public function procedure(){
    	return $this->belongsTo("App\ProcedureEntry","procedure_entry_id");
    }

    public function responsible(){
    	return $this->belongsTo("App\User","responsible_id");
    }
}
