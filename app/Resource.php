<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    protected $table = 'resource';
    protected $fillable = ['resourcekey', 'value_en', 'value_ge', 'value_ru'];

    public static function getResourceForLanguage($lang)
    {
      $host = "10.79.79.40";
      $conn = pg_pconnect("host=" . $host . " port=52461 dbname=gpscontrol user=gpscontrol password=fDc5DeF23SdadXdC457GbvS")
        or die("Connection failed" . pg_last_error());
  
      $lang = 'value_'.$lang;
      $sql = "SELECT resourcekey, ".$lang." FROM resource WHERE deleted = 'f'";
      $result = pg_query($conn, $sql);
  
      $language_resource = [];
  
      while($row = pg_fetch_assoc($result)) {
        $language_resource[$row['resourcekey']] = $row[$lang];
      }

      session()->put('langauge_resource', $language_resource);
  
      return $language_resource;
    }
}
