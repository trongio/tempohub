<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseRoomSensor extends Model
{
    protected $table = 'tempohub_roomsensors';

    protected $fillable = ['roomid', 'map_x', 'map_y', 'sensorid'];

    public 	  $timestamps 	= false;

    public function sensor() 
    {
        return $this -> belongsTo('App\Sensor', 'sensorid', 'id');
    }

    public function room()
    {
    	return $this -> belongsTo('App\WarehouseRoom','roomid','id');
    }

}
