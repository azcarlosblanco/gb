<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    protected $table = 'currency';
    protected $fillable = ['name', 'display_name'];
    public $timestamps = false;

    public static function covertToUSD($amount, $curr_id){
      //TODO actually do the math
      return $amount;
    }
}
