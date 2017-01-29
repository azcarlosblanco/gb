<?php namespace Modules\Utilities\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class City extends Model
{
 	protected $table = 'city';
    use SoftDeletes;

    protected $fillable = [
        			'name',
    					];
}
