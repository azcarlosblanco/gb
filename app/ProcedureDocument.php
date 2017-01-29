<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProcedureDocument extends Model
{
    protected $table = 'procedure_document';
    protected $fillable = array('name', 'description', 'type', 'procedure_catalog_id');
}
