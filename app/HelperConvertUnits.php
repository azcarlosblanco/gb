<?php

namespace App;

use DB;

class HelperConvertUnits
{
  const RKGLB = 2.20462;
  const RMCM = 100;

  //units in meters with decimal separate by dot (.)
  public static function metersToCm($value){
    return round( ($value * self::RMCM), 2);
  }

  //units in cm with decimal separate by dot (.)
  public static function cmToMeters($value){
    return round( ($value / self::RMCM), 2);
  }

  //units in kg with decimal separate by dot (.)
  public static function kgToLb($value){
    return round( ($value * self::RKGLB), 2);
  }

  //units in lb with decimal separate by dot (.)
  public static function LbToKg($value){
    return round( ($value / self::RKGLB), 2);
  }

  public static function secondsToTime($init){
    $hours = floor($init / 3600);
    if($hours<10){
      $hours="0".$hours;
    }
    $minutes = floor(($init / 60) % 60);
    if($minutes<10){
      $minutes="0".$minutes;
    }
    $seconds = $init % 60;
    if($seconds<10){
      $seconds="0".$seconds;
    }
    return "$hours:$minutes:$seconds";
  }
  
}