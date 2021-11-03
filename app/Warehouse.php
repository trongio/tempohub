<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    protected $table = 'tempohub_warehouses';

    protected $fillable = ['name','firmaid','isactive'];

    public 	  $timestamps 	= false;

    public function warehouse_rooms()
    {
    	return $this -> hasMany('App\WarehouseRoom','warehouseid');
    }
}
