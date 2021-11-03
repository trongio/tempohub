<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Alert2User extends Model
{
    protected $table = 'alerts2users';

    protected $fillable = [
    	'alertsid',
    	'username'
    ];
    public 	  $timestamps 	= false;
}
