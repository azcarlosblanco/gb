<?php namespace Modules\Utilities\Entities;

use DB;
use Modules\Utilities\Entities\City;

class Location
{
  public static function getCountriesList(){
    $lst = DB::table('country')->lists('name','id');
    return $lst;
  }

  public static function getStatesList(){
    //$states = array();
    $lst = DB::table('state')->lists('name','id');

    /*foreach( $lst as $x ){
      $id = $x->id;
      $name = $x->name;
      //$states[$x->country_id][] = array($id => $name);
      $states[$x->country_id][$id] = $name;
    }*/

    return $lst;
  }

  public static function getCitiesList(){
    /*$cities = array();
    $lst = DB::table('city')->get();

    foreach( $lst as $x ){
      $id = $x->id;
      $name = $x->name;
      $states[$x->state_id] = array($id => $name);*/

      $states=City::all()->pluck('name','id');
   // }

    return $states;
  }
}
