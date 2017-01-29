<?php namespace Modules\Utilities\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Province extends Model
{
    protected $table = 'province';
    use SoftDeletes;

    protected $fillable = [
        			'name',
    					];
}
