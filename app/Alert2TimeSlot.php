<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert2TimeSlot extends Model
{
    protected $table = 'timeslot4alerts';

    protected $fillable = [
    	'alertsid',
    	'timefrom',
    	'timeto',
    	'weekdays'
    ];

    public 	  $timestamps 	= false;
}
