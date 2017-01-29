<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Diagnosis extends Model
{
  protected $table = 'diagnosis';
  protected $fillable = ['name', 'display_name'];
  public $timestamps = false;
}
