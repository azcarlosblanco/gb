<?php namespace Modules\Claim\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClaimConcept extends Model {

  protected $table = 'claim_concept';
  protected $fillable = ['name', 'display_name', 'notify', 'deduct_discount'];
  public $timestamps = false;

}
