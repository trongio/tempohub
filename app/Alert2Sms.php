<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert2Sms extends Model
{
    protected $table = 'sms4alerts';

    protected $fillable = [
    	'alertsid',
    	'telephon'
    ];

    public 	  $timestamps 	= false;
}
