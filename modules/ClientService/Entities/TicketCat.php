<?php namespace Modules\Clientservice\Entities;
   
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketCat extends Model {

    protected $table="ticket_cat";

    protected $fillable = [ 'name', 
    						'display_name',
    						'category', 
    						];

     use SoftDeletes;

    public function ticket(){
		return $this->hasOne('Modules\ClientService\Entities\TicketCat',
										'ticket_cat_id');
	}

	public function ticketCatRole(){
		return $this->belongsToMany('Modules\Authorization\Entities\Role',
										'ticket_cat_role',
										'ticket_cat_id',
										'role_id'); 
	}

}