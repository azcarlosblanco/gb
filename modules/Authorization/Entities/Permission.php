<?php namespace Modules\Authorization\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
	protected $table = 'permission';
	use SoftDeletes;

	public function roles()
	{
		return $this->belongsToMany('Modules\Authorization\Entities\Role',
									'permission_role',
									'permission_id',
									'role_id');
	}

	public function menus()
	{
		return $this->belongsToMany('Modules\Menu\Entities\Menu',
									'menu_permission',
									'permission_id',
									'menu_id');
	}
}