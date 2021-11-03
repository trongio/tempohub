<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Temperature extends Model
{
   	protected $table = 'temperaturedata_back';

   	public function transmission()
   	{
   		return $this -> belongsTo('App\Transmission','transmissionid');
   	}

   	public function sensor()
   	{
   		return $this -> belongsTo('App\WarehouseRoomSensor','index','temperaturedata_back_index');
   	}

}
