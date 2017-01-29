<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileEntryTemp extends Model
{
  protected $table = 'file_entry_temp';

  protected $fillable = [
              'filename',
              'mime',
              'original_filename',
              'description',
              'driver',
              'complete_path',
              'data'
              ];
}
