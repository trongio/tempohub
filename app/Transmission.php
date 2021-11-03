<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transmission extends Model
{
  	protected $table = 'transmission';

  	public function temperature_data_backs()
  	{
  		return $this -> hasMany('App\Temperature','transmissionid','id');
  	}
}
