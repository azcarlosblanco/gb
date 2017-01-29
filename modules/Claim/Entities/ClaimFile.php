<?php namespace Modules\Claim\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use JWTAuth;

class ClaimFile extends Model
{
	protected $table='claim_file';

	//type determine if the agent is a 'agent' or a 'subagent'
	protected $fillable=[
							'description',
							'file_entry_id',
							'claim_id',
              				'procedure_document_id',
							'date_invoice',
							'amount',
							'concept',
							'supplier_id',
							'usa',
							'currency_id',
							'prev_order'
						];

	//to enable soft delete in the model
    use SoftDeletes;

    public function procedureDocument(){
    	return $this->belongsTo('App\ProcedureDocument','procedure_document_id','id');
    }

    public function fileEntry(){
    	return $this->belongsTo('App\FileEntry','file_entry_id','id');
    }

		public function claim(){
    	return $this->belongsTo('Modules\Claim\Entities\Claim','claim_id','id');
    }

    public function supplier(){
    	return $this->belongsTo('Modules\Supplier\Entities\Supplier','supplier_id','id');
    }

		public function settlement(){
			return $this->hasOne('Modules\Claim\Entities\ClaimSettlement', 'claim_file_id', 'id');
		}

		public function currency(){
    	return $this->belongsTo('App\Currency','currency_id','id');
    }

		public function getConcept(){
    	return $this->belongsTo('Modules\Claim\Entities\ClaimConcept','concept','id');
    }

}
