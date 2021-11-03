<?php

namespace App;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
   	protected $table = 'alerts';

    protected $fillable = [
    	'alertstypeid',
    	'firmaid',
    	'name',
    	'value1',
    	'value2',
    	'value3',
    	'value4',
    	'value5',
    	'allusers',
    	'sound',
    	'popup',
    	'defaultsms',
    	'defaultemail',
    	'showalert'
    ];
    
    /*    
        alerts:

        value1 – 1 - ტემპერატურის აწევისას, 0 - ტემპერატურის დაწევისას

        value2 - ტემპერატურული ზღვარი

        value3 - გადაცილების დაშვებული პერიოდი წუთებში

        value4 -დაჩიკის ინდექსი (ეს პარამეტრი საჭიროა, მხოლოდ მაცივრების შემთხვევაში, წინააღმდეგ შემთვევაში იქნება NULL. თუ საჭიროა ყველა დაჩიკის კონტროლი, მაშინ უნდა ეწეროს -1)

    */

    public 	  $timestamps 	= false;

    public function alert_type()
    {
        return $this -> belongsTo('App\AlertType','alertstypeid','id');
    }

    public function device()
    {
        return $this -> belongsTo('App\Alert2Device','id','alertsid');
    }

    public function sensor()
    {
        return $this -> hasOne('App\Sensor', 'index', 'value4');
    }

    public function users()
    {
        return $this -> hasMany('App\Alert2User','alertsid');
    }

    public function phones()
    {   
        return $this -> hasMany('App\Alert2Sms','alertsid');
    }

    public function emails()
    {   
        return $this -> hasMany('App\Alert2Email','alertsid');
    }
    
    public function time()
    {
        return $this -> belongsTo('App\Alert2TimeSlot','id','alertsid');
    }

    public function alertsdata()
    {
    	return $this -> hasMany('App\AlertsRecived','alertsid')->where('readed', false);
    }

    public function getNonReadedAndTodayAllerts()
    {
        $today = Carbon::today()->toDateString();
    	return $this -> hasMany('App\AlertsRecived','alertsid')->where(function($q) {
            $today = Carbon::today()->toDateString();
            $q->where('datestamp', '>', $today)->orWhere('readed', false);
        });
    }
}
