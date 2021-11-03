<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $table = 'device';


    public function sensors()
    {
    	return $this -> hasMany('App\Sensor2Device','imei','device');
    }

    public function device_sensors()
    {
    	return $this -> hasMany('App\Sensor','imei','imei');
    }

   	public function transmissions()
   	{
   		return $this -> hasMany('App\Transmission','imei','imei');
   	}
}
