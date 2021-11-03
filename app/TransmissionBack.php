<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransmissionBack extends Model
{
  	protected $table = 'transmission_back';

  	public function temperature_data_backs()
  	{
  		return $this -> hasMany('App\Temperature','transmissionid','id');
  	}

}
