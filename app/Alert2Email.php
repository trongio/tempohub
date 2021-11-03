<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert2Email extends Model
{
    protected $table = 'email4alerts';

    protected $fillable = [
    	'alertsid',
    	'email'
    ];

    public 	  $timestamps 	= false;
}
