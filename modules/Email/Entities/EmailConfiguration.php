<?php namespace Modules\Email\Entities;

use Illuminate\Database\Eloquent\Model;

class EmailConfiguration extends Model
{
	protected $table = 'email_configuration';

	protected $fillable = [
							'name',
							'description',
							'value',
							'company_id'
						  ];

	public function scopteName($query,$name){
		return $query->where('name',$name)
	}

}