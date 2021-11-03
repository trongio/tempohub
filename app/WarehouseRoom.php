<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WarehouseRoom extends Model
{
    protected $table = 'tempohub_rooms';

    protected $fillable = ['id','warehouseid','name','isactive','image'];

    public 	  $timestamps 	= false;

    public function warehouse_roomsensors()
    {
    	return $this -> hasMany('App\WarehouseRoomSensor','roomid');
    }

    public function warehouse()
    {
    	return $this -> belongsTo('App\Warehouse','warehouseid','id');
    }

    public function sensors()
    {
        return $this->belongsToMany(Sensor::class, 'tempohub_roomsensors', 'roomid', 'sensorid');
    }

    public static function controllers($roomid) {
        $host = "10.79.79.40";
        $conn = pg_pconnect("host=" . $host . " port=52461 dbname=gpscontrol user=gpscontrol password=fDc5DeF23SdadXdC457GbvS")
        or die("Connection failed" . pg_last_error());
        $arr = [];
        $sql = "SELECT DISTINCT(imei) from tempohub_rooms as r
        left join tempohub_roomsensors tr on tr.roomid = r.id
        left join tempohub_sensors s on s.id = tr.sensorid
        where r.id = ".$roomid;

        $result = pg_query($conn, $sql);

        while($row = pg_fetch_assoc($result)) {
          array_push($arr,  $row['imei']);
        }

        return $arr;
    }
}
