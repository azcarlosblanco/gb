<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuizItem extends Model
{
    protected $table = 'quiz_item';

    use SoftDeletes;

}
