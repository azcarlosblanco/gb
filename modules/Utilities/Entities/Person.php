<?php namespace Modules\Utilities\Entities;

use DB;

class Person
{
    public static function getSexList(){
      $lst = DB::table('person_sex')->lists('name','id');
      return $lst;
    }

    public static function getStatusList(){
      $lst = DB::table('person_status')->lists('name','id');
      return $lst;
    }

    public static function getDoctypeList(){
      $lst = DB::table('person_doctype')->lists('name','id');
      return $lst;
    }
}
