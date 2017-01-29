<?php namespace Modules\GuiaEnvio\Entities;
   
use Illuminate\Database\Eloquent\Model;

/**
 * Phisical docuements that need to be send to others
 */
class SendDocument extends Model {

	protected $table="send_document";

    protected $fillable = [ 
    						//procedures,others
    						'reason',
							//the id of a register user
							'sender',
							//id of the receiver
							'receiver_id',
							'receiver_type', //agent,ic (insurance company)
							//if receiver_id is null the following are compulsory
							'receiver_name',
							'receiver_address',
							'receiver_phone',
							//process that is related to this document
							//can be null
							'process_id',
							'state'
    						];

    public function items(){
    	return $this->hasMany('Modules\GuiaEnvio\Entities\SendDocumentItem','send_document_id','id');
    }

}