<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FileEntry extends Model
{
	protected $table = 'file_entry';

	protected $fillable = [
							'filename',
							'mime',
							'original_filename',
							'table_type',
							'table_id',
							'description',
							'driver',
							'complete_path',
							'data',
							'status'
						  ];


    public function ticketDetailAttach(){
		return $this->belongsToMany('Modules\ClientService\Entities\TicketDetail',
									'ticket_attach',
									'file_entry_id',
									'ticket_detail_id'); 
	}

	public function saveWithDefaults($params){
		foreach( $this->fillable as $field  ){
			$val = ($field == 'status') ? 1 : '';
			$this->$field = array_get($params, $field, $val);
		}

		$this->save();
	}

}
