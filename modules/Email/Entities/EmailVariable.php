<?php namespace Modules\Email\Entities;

use Illuminate\Database\Eloquent\Model;

class EmailVariable extends Model
{
	protected $table = 'email_variable';

	protected $fillable = [
							'sender',
							'domain',
							'company_id'
						  ];

}