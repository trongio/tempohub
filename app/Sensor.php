<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
   	protected $table = 'tempohub_sensors';

    protected $fillable = ['id', 'name', 'index', 'isactive'];

    public $timestamps = false;

    public function roomToSensor() {
        return $this -> hasOne('App\WarehouseRoomSensor', 'sensorid');
    }

    public function controller() {
        return $this -> belongsTo('App\SensorsController', 'imei', 'imei');
    }

    public function device() {
        return $this -> belongsTo('App\Device', 'imei', 'imei');
    }

    public function transmission() {
        return $this -> belongsTo('App\Transmission', 'imei', 'imei');
    }

    public function alertsdata() {
        return $this -> hasMany('App\AlertsRecived', 'imei', 'imei')->where('value4', $this->index);
    }
}
