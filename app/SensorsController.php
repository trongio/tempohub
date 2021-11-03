<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SensorsController extends Model
{
   	protected $table = 'tempohub_controllers';

    protected $fillable = [];

    public function warehouse() {
        return $this -> belongsTo('App\Warehouse', 'warehouseid', 'id');
    }

    public function sensors() {
        return $this -> hasMany('App\Sensor', 'imei', 'imei');
    }
}
