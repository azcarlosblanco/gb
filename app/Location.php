<?php

namespace App;

use DB;
use Modules\Utilities\Entities\State;

class Location
{
  public static function getCountriesList(){
    $lst = DB::table('country')->lists('name','id');
    return $lst;
  }

  public static function getStatesList(){
    $states = array();
    $lst = DB::table('state')->get();

    foreach( $lst as $x ){
      $id = $x->id;
      $name = $x->name;
      //$states[$x->country_id][] = array($id => $name);
      $states[$x->country_id][$id] = $name;
    }

    return $states;
  }


  public static function getCitiesList(){
    $cities = array();
    $lstState = State::all();
    foreach ($lstState as $lstS) {
      $cities["".$lstS->id] = Location::getCities($lstS->id);
    }
    return $cities;
  }


  public static function getCities($idState){
    $cities = State::find($idState);
    return $cities->cities->pluck('name','id');
  }


}
