<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert2Device extends Model
{
    protected $table = 'alerts2devices';

    protected $fillable = [
    	'alertsid',
    	'imei'
    ];

    public 	  $timestamps 	= false;

    public function device()
    {
    	return $this -> belongsTo('App\Device','imei','imei');
    }
}
