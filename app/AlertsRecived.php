<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AlertsRecived extends Model
{
    protected $table = 'alertsdata';

    protected $fillable = [
    	'readed'
    ];

    public 	  $timestamps 	= false;

    public function alert()
    {
    	return $this -> belongsTo('App\Alert','alertsid','id');
    }

    public function device()
    {
    	return $this -> belongsTo('App\Device','imei','imei');
    }

    public function controller()
    {
        return $this -> belongsTo('App\SensorsController', 'imei', 'imei');
    }

    public function sensor() 
    {
        return $this -> belongsTo('App\Sensor', 'imei', 'imei');
    }
}
