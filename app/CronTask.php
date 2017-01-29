<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CronTask extends Model
{
    protected $table = 'cron_task';
    use SoftDeletes;

  	protected $fillable = ['type', 'data', 'action', 'status', 'date_expire', 'table_type', 'table_id'];

    public function finishWithSuccess(){
      $this->status = 1;
      $this->save();
    }

    public function finishWithError(){
      $this->status = 2;
      $this->save();
    }
}
