<?php

namespace App\Helpers;

class Functions
{

  public static function time2readable($seconds) {
    $string = "";
    $days = intval(intval($seconds) / (3600*24));
    $hours = (intval($seconds) / 3600) % 24;
    $minutes = (intval($seconds) / 60) % 60;
    $seconds = (intval($seconds)) % 60;
    $langauge_resource = session()->get('langauge_resource');
    if($days> 0){
      $string .= $days . " " . strtolower($langauge_resource['day']) . " ";
    }
    if($hours > 0){
      $string .= $hours . " " . strtolower($langauge_resource['hour']) . " ";
    }
    if($days == 0 && $minutes > 0){
      $string .= $minutes . " " . strtolower($langauge_resource['minute_short']) . " ";
    }
    if ($days == 0 && $hours == 0 && $seconds > 0){
      $string .= $seconds . " " . strtolower($langauge_resource['sec']) . " ";
    }
  
    return trim($string);
  }

  public static function getBinaryFromWeekdays($weedays)
  {

    $monday     = 0;
    $tuesday    = 0;
    $wednesday  = 0;
    $thursday   = 0;
    $friday     = 0;
    $saturday   = 0;
    $sunday     = 0;
    
    foreach ($weedays as $key => $value) {
      if($key == 'mon')
      {
        if($value == 'on')
        {
          $monday = 1;
        }
      }
      if($key == 'tue')
      {
        if($value == 'on')
        {
          $tuesday = 1;
        }
      }
      if($key == 'wed')
      {
        if($value == 'on')
        {
          $wednesday = 1;
        }
      }
      if($key == 'thu')
      {
        if($value == 'on')
        {
          $thursday = 1;
        }
      }
      if($key == 'fri')
      {
        if($value == 'on')
        {
          $friday = 1;
        }
      }
      if($key == 'sat')
      {
        if($value == 'on')
        {
          $saturday = 1;
        }
      }
      if($key == 'sun')
      {
        if($value == 'on')
        {
          $sunday = 1;
        }
      }
    } 
    

    $weekdays = $sunday.$saturday.$friday.$thursday.$wednesday.$tuesday.$monday;

    return $weekdays;
  }


  public static function getWSDLLink() {
    $host = "10.79.79.32";

    return "http://" . $host . ":4141/wsdlServices?wsdl";
  }

}

  