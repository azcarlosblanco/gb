<?php namespace Modules\Policy\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteCode extends Model {

  protected $table = "quote_code";
  public $timestamps = false;
  protected $fillable = [
                "table_type",
                "table_id",
                "value",
                "insurance_company_id"
              ];
  use SoftDeletes;

  public static function getValue(Model $obj, $insur_id){
    $val = self::where('table_type', $obj->getTable())
               ->where('table_id', $obj->id)
               ->where('insurance_company_id', $insur_id)
               ->value('value');
    return $val;
  }

  public static function getValueFromID($table_type, $table_id, $insur_id){
    $val = self::where('table_type', $table_type)
               ->where('table_id', $table_id)
               ->where('insurance_company_id', $insur_id)
               ->value('value');
    return $val;
  }

}
