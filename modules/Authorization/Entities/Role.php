<?php namespace Modules\Authorization\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    protected $table = 'role';
    use SoftDeletes;
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'display_name', 'description'];

    public function users()
    {
        return $this->belongsToMany('App\User',
                                        'role_user',
                                        'role_id',
                                        'user_id');
    }

    public function permissions()
    {
        return $this->belongsToMany('Modules\Authorization\Entities\Permission',
                                        'permission_role',
                                        'role_id',
                                        'permission_id');
    }

    public function ticketCat()
    {
        return $this->belongsToMany('Modules\ClientService\Entities\TicketCat',
                                        'ticket_cat_role',
                                        'role_id',
                                        'ticket_cat_id');
    }

}