<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileUploadEntry extends Model
{
    protected $table = 'file_upload_entry';
    protected $fillable = ['expected', 'completed', 'table_type', 'table_id', 'data'];

    public static function incrementCompleted($ttype, $tid){
      $sql = 'update file_upload_entry set completed = completed+1 where table_type = ? and table_id = ?';
      $affected = \DB::update($sql, [$ttype, $tid]);
      return $affected;
    }
}
