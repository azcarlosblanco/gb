<?php namespace Modules\GuiaEnvio\Entities;
   
use Illuminate\Database\Eloquent\Model;

class SendDocumentItem extends Model {

	protected $table="send_document_item";

    protected $fillable = [ 
    						'description',
    						'num_copies',
    						'send_document_id'
    						];

    public function sendDocument(){
    	return $this->belongsTo('Modules\GuiaEnvio\Entities\SendDocument','send_document_id','id');
    }
}