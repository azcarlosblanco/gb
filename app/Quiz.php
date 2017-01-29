<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quiz extends Model
{
    protected $table = 'quiz';

    use SoftDeletes;

    public function items(){
  		return $this->hasMany('App\QuizItem','quiz_id','id');
  	}
}
