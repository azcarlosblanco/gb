<?php namespace Modules\GuiaEnvio\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuiaEnvio extends Model 
{
	protected $table="guia_remision";

	protected $fillable = [
							'date',
							'track_number',
							'reason',
							'sender',
							'receiver_name',
							'receiver_phone',
							'receiver_country',
							'receiver_city',
							'receiver_post_code',
							'receiver_address',
							'carrier_id',
							'external_track_number',
							'foreign_id',
							'file_entry_id',
						  ];

	public function items(){
    	return $this->hasMany('Modules\GuiaEnvio\Entities\GuiaEnvioItem','send_document_id','id');
    }

    public function process()
    {
        return $this->belongsToMany('App\ProcessEntry','guia_remision_process','guia_remision_id','process_id');
    }
}