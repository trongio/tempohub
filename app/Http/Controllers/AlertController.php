<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\WarehouseRoomSensor;
use App\User;
use App\Alert;
use App\Alert2Device;
use App\Alert2User;
use App\Helpers\Functions;
use App\Alert2TimeSlot;
use App\Alert2Sms;
use App\Alert2Email;
use SoapClient;
use SoapFault;
use App\AlertsRecived;
use App\Resource;
use App\Warehouse;

class AlertController extends Controller
{

	public function getAlertDeviceSensors(Request $request)
	{       
		$lang           = Auth::user()->language;
		$language_resource = Resource::getResourceForLanguage($lang);
	    $sensors        = WarehouseRoomSensor::where([['imei',$request -> device_id],['status',true]]) -> get();

	    $output_sensors =  '<label>Sensors</label>';
	    $output_sensors .= '<select class="form-control" name="alert_sensors">';

	    $output_sensors .= '<option value="-1">'.$language_resource['all'].'</option>';

	    foreach($sensors as $sensor)
	    {
	        $output_sensors .= '<option value="'.$sensor -> temperaturedata_back_index.'">'.$sensor -> sensor_name.'</option>';
	    }

	    $output_sensors .= '</select>';
	    
		return response()->json([
	      'success'   =>'ok',
	      'sensors'   => $output_sensors
	    ]);
	}

	public function postAddNewAlert(Request $request)
	{
	    $user                 = Auth::user();
		$lang                 = Auth::user()->language;
		$language_resource    = Resource::getResourceForLanguage($lang);

	    $alert_type_id        = 11; //Temperature alert type from documentation: https://casatrade.atlassian.net/wiki/spaces/GPSCON/pages/249921544/Alerts
	    $firmaid              = $user -> firmaid;
	    $alert_name           = $request -> alert_name;
	    $alert_limit          = $request -> alert_limit;
	    $alert_type           = $request -> alert_type;
	    $alert_time           = $request -> alert_time;
	    $alert_device         = $request -> alert_device;
	    $alert_sensor         = $request -> alert_sensor;
	    $alert_sound          = (isset($request -> alert_sound)) ? $request -> alert_sound : false;
	    $alert_popup          = (isset($request -> alert_popup)) ? $request -> alert_popup : false;
	    $alert_sms_standard   = $request -> alert_default_sms_standard;
	    $alert_sms_text       = $request -> alert_sms_text;
	    $alert_phones         = $request -> alert_phones;
	    $alert_emails         = $request -> alert_emails;
	    $alert_email_standard = $request -> alert_default_email_standard;
	    $alert_email_text     = $request -> alert_email_text;
	    $alert_date_from      = $request -> alert_date_from;
	    $alert_date_to        = $request -> alert_date_to;
	    $weekday              = $request -> weekday;
	    $employers            = $request -> employers;

	    $weekdays = Functions::getBinaryFromWeekdays($weekday);

	    $decimal_weekdays = bindec($weekdays);

	    if($alert_sms_standard == false)
	    {
	      $defaultsms = $alert_sms_text;
	    }else{
	      $defaultsms = null;
	    }

	    if($alert_email_standard == false)
	    {
	      $defaultemail = $alert_email_text;
	    }else{
	      $defaultemail = null;
	    }

	    $alert = Alert::create([
	      'alertstypeid' => $alert_type_id,
	      'firmaid'      => $firmaid,
	      'name'         => $alert_name,
	      'value1'       => $alert_type,
	      'value2'       => $alert_limit,
	      'value3'       => $alert_time,
	      'value4'       => $alert_sensor,
	      'allusers'     => true,
	      'sound'        => $alert_sound,
	      'popup'        => $alert_popup,
	      'defaultsms'   => $defaultsms,
	      'defaultemail' => $defaultemail
	    ]);

	    $alerts_to_device = Alert2Device::create([
	      'alertsid'  => $alert -> id,
	      'imei'      => $alert_device
	    ]);

	    foreach($employers as $employer)
	    {
	      $alerts_to_user = Alert2User::create([
	        'alertsid'  => $alert -> id,
	        'username'  => $employer
	      ]);
	    }
		if(!is_null($alert_date_from) && !is_null($alert_date_to) && !is_null($decimal_weekdays)) {
			$alert_to_time_slot = Alert2TimeSlot::create([
				'alertsid'  => $alert -> id,
				'timefrom'  => $alert_date_from,
				'timeto'    => $alert_date_to,
				'weekdays'  => $decimal_weekdays
			]);
		}
	    

	    for($i = 0; $i < sizeof($alert_phones['code']); $i++)
	    {
			if(!is_null($alert_phones['number'][$i])) {
				$alert_to_sms  = Alert2Sms::create([
					'alertsid'  => $alert -> id,
					'telephon'  => $alert_phones['code'][$i] . $alert_phones['number'][$i]
				]);
			}
	    }

	    foreach($alert_emails as $alert_email)
	    {
			if(!is_null($alert_email)) {
				$alerts_to_email = Alert2Email::create([
					'alertsid'  => $alert -> id,
					'email'     => $alert_email
			    ]);
			}
	    }
		
		$client = new SoapClient("http://10.79.79.32:4141/wsdlServices?wsdl");
		$client->command("#UPDATEALERT>" . $alert->id);

		$warehouses = Warehouse::where('firmaid', $user->firmaid)->get();
		$employers = User::where('firmaid',$user -> firmaid) -> get();

		$html  = '';
		$html .= '<tr data-alert-id="'.$alert->id.'">';
		$html .= '<td>'.$alert -> name.'</td>';
		$html .= '<td class="center">'.$alert -> alert_type -> description.'</td>';
		$html .= '<td class="center">'.$alert -> device -> device -> name.'</td>';
		$html .= '<td class="center">'.$alert -> users -> count().'</td>';
		$html .= ($alert -> sound == 1) ? '<td class="center">'.$language_resource['yes'].'</td>' : '<td class="center">'.$language_resource['no'].'</td>';
		$html .= ($alert -> popup == 1) ? '<td class="center">'.$language_resource['yes'].'</td>' : '<td class="center">'.$language_resource['no'].'</td>';
		$html .= '<td class="td_buttons">';
		$html .= '<button class="btn btn-primary edit_alert" id="'.$alert->id.'" data-toggle="modal" data-target="#edit_alert_'.$alert->id.'">'.$language_resource['edit'].'</button>';
		$html .= '<div class="modal fade" id="edit_alert_'.$alert->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
		$html .= '<div class="modal-dialog modal-lg modal-dialog-centered" role="document">';
		$html .= '<div class="modal-content">';
		$html .= '<div class="modal-header">';
		$html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		$html .= '<h4 class="modal-title" id="myModalLabel">'.$language_resource['edit'].'</h4>';
		$html .= '</div>';
		$html .= '<div class="modal-body">';
		$html .= '<form class="edit_warehouse_room_sensor_alert_form" id="edit_warehouse_room_sensor_alert_form_'.$alert->id.'" method="POST" class="alerts_target">';
		$html .= '<input type="hidden" name="alert_id" value="'.$alert->id.'">';
		$html .= '<div id="smartwizardedit" class="smartwizard smartwizardedit" data-alert-id="'.$alert->id.'">';
		$html .= '<ul>';
		$html .= '<li><a href="#step-1"><small>'.$language_resource['configuration'].'</small></a></li>';
		$html .= '<li><a href="#step-2"><small>'.$language_resource['devices'].'</small></a></li>';
		$html .= '<li><a href="#step-3"><small>'.$language_resource['notifications'].'</small></a></li>';
		$html .= '<li><a href="#step-4"><small>'.$language_resource['schedule'].'</small></a></li>';
		$html .= '<li><a href="#step-5"><small>'.$language_resource['users'].'</small></a></li>';
		$html .= '</ul>';
		$html .= '<div>';
		// კონფიგურაცია
		$html .= '<div id="step-1">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-6"> ';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['desname'].'</label>';
		$html .= '<input type="text" class="form-control" name="alert_name" value="'.$alert -> name .'" required> ';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['alarm'].'</label>';
		$html .= '<select class="form-control" name="alert_type">';
		$html .= ($alert -> value3 == 1) ? '<option value="1">'.$language_resource['on-temp-rise'].'</option><option value="1">'.$language_resource['on-temp-decrease'].'</option>' : '<option value="0">'.$language_resource['on-temp-decrease'].'</option><option value="1">'.$language_resource['on-temp-rise'].'</option>';
		$html .= '</select>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="row mt-3">';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['bound'].' (°C):</label>';
		$html .= '<input type="number" class="form-control" name="alert_limit" required value="'.$alert -> value2.'"> ';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['exceeding-allowed-time'].' ('.$language_resource['minute'].'):</label>';
		$html .= '<input type="number" class="form-control" name="alert_time" min="1" step="1" max="60" required value="'.$alert -> value3.'"> ';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		// მოწყობილობა
		$html .= '<div id="step-2">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-12">';
		$html .= '<div class="form-group">';
		$html .= $alert -> sensor['controller']['warehouse']['name'] . ' - ' . $alert -> device -> name . ' - ' .  $alert -> sensor['name'];
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['warehouse'].'</label>';
		$html .= '<select class="form-control alerts_warehouse" name="alert_warehouse" id="warehouse_list_edit" data-alert-id="'.$alert -> id.'">';
		$html .= '<option value="0">'.$language_resource['warehouse_select'].'</option>';
		foreach ($warehouses as $warehouse) {
			$html .= '<option value="'.$warehouse -> id.'">'.$warehouse -> name.'</option>';
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['device'].'</label>';
		$html .= '<select class="form-control alerts_device" name="alert_device" id="device_list_'.$alert->id.'" data-alert-id="'.$alert->id.'" disabled>';
		$html .= '<option value="0">'.$language_resource['selectvehicles'].'</option>';
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['sensor'].'</label>';
		$html .= '<select class="form-control alerts_device" name="alert_sensor" id="sensor_list_'.$alert->id.'" disabled>';
		$html .= '<option value="0">'.$language_resource['choose-sensor'].'</option>';
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<span class="text-danger">*თუ გსურთ რომ განგაში გავრცელდეს მოწყობილობაზე მიბმული ყველა სენსორისათვის, სენსორების ასარჩევი ველი დატოვეთ შეუვსებელი</span>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		// შეტყობინებები
		$html .= '<div id="step-3" class="">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-12"> ';
		$html .= '<div class="form-group">';
		$html .= '<div class="col-md-12">  ';
		$html .= '<label>'.$language_resource['alert-accompaniment'].': </label>';
		$html .= '<div class="col-md-12 padding">';
		$html .= '<label class="checkbox-inline m-r-20">';
		$html .= ($alert -> sound == 1) ? '<input type="checkbox" name="alert_sound" value="true" checked>' . $language_resource['sound_signal'] : '<input type="checkbox" name="alert_sound" value="true">' . $language_resource['sound_signal'];
		$html .= '</label>';
		$html .= '<label class="checkbox-inline">';
		$html .= ($alert -> popup == 1) ? '<input type="checkbox" name="alert_popup" value="true" checked>' . $language_resource['popup-window'] : '<input type="checkbox" name="alert_popup" value="true">' . $language_resource['popup-window'];
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>SMS:</label>';
		$html .= '<div class="col-md-12 padding margin">';
		$html .= '<label>';
		$html .= '<small>';
		$html .= ($alert -> defaultsms == null) ? '<input type="radio" name="alert_default_sms_standard" class="m-r-5" value="true" checked="">' . $language_resource['standard'] : '<input type="radio" name="alert_default_sms_standard" class="m-r-5" value="false">' . $language_resource['standard'];
		$html .= '</small>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '<div class="col-md-12 padding margin">';
		$html .= '<label>';
		$html .= '<small>';
		$html .= ($alert -> defaultsms != null) ? '<input type="radio" name="alert_default_sms_standard" class="m-r-5 sms_radio" value="true" checked="">' . $language_resource['template'] : '<input type="radio" name="alert_default_sms_standard" class="m-r-5 sms_radio" value="false">' . $language_resource['template'];
		$html .= '</small>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-group sms_textarea_form_group">';
		$html .= '<div class="col-md-12 padding">';
		$html .= '<label><i class="fa fa-wrench"></i> '.$language_resource['constructor'].'</label>';
		$html .= '<textarea class="form-control" rows="2" name="alert_sms_text">';
		$html .= ($alert -> defaultsms != null) ? $alert -> defaultsms : '';
		$html .= '</textarea>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['sms-recipients'].':</label>';
		foreach($alert -> phones as $phone) {
			$html .= '<div class="col-md-12 padding margin">';
			$html .= '<div class="input-group input-group-sm m-t-5 sms-receiver display-flex">';
			$html .= '<select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">';
			$html .= (substr($phone -> telephon,0,3) == '995') ? '<option value="995">+995</option><option value="374">+374</option>' : '<option value="374">+374</option><option value="995">+995</option>';
			$html .= '</select>';
			$html .= '<input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX" value="'.substr($phone -> telephon,3).'">';
			$html .= '<div class="input-group-btn"></div>';
			$html .= '</div>';
			$html .= '</div>';
		}
		if($alert -> phones -> count() < 3) {
			for($i = $alert -> phones -> count(); $i < 3; $i++) {
				$html .= '<div class="col-md-12 padding margin">';
				$html .= '<div class="input-group input-group-sm m-t-5 sms-receiver display-flex">';
				$html .= '<select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">';
				$html .= '<option value="995">+995</option>';
				$html .= '<option value="374">+374</option>';
				$html .= '<input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX">';
				$html .= '<div class="input-group-btn"></div>';
				$html .= '</div>';
				$html .= '</div>';
			}
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['email'].':</label>';
		$html .= '<div class="col-md-12 padding margin">';
		$html .= '<label>';
		$html .= '<small>';
		$html .= ($alert -> defaultemail == null) ? '<input type="radio" name="alert_default_email_standard" class="m-r-5" value="true" checked="">' . $language_resource['standard'] : '<input type="radio" name="alert_default_email_standard" class="m-r-5" value="false">' . $language_resource['standard'];
		$html .= '</small>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '<div class="col-md-12 padding margin">';
		$html .= '<label>';
		$html .= '<small>';
		$html .= ($alert -> defaultemail != null) ? '<input type="radio" name="alert_default_email_standard" class="m-r-5 email_radio" value="true" checked="">' . $language_resource['template'] : '<input type="radio" name="alert_default_email_standard" class="m-r-5 email_radio" value="false">' . $language_resource['template'];
		$html .= '</small>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-group email_textarea_form_group">';
		$html .= '<div class="col-md-12 padding">';
		$html .= '<label><i class="fa fa-wrench"></i> '.$language_resource['constructor'].'</label>';
		$html .= '<textarea class="form-control" rows="2" name="alert_email_text">';
		$html .= ($alert -> defaultemail != null) ? $alert -> defaultemail : '';
		$html .= '</textarea>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['email-recipients'].':</label>';
		foreach($alert -> emails as $email) {
			$html .= '<div class="col-md-12 padding margin">';
			$html .= '<div class="input-group input-group-sm m-t-5 sms-receiver display-flex">';
			$html .= '<input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="'.$language_resource['email'].'" value="'.$email -> email.'">';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '';
			$html .= '';
			$html .= '';
			$html .= '';
		}
		if($alert -> emails -> count() < 3) {
			for($i = $alert -> emails -> count(); $i < 3; $i++) {
				$html .= '<div class="col-md-12 padding margin">';
				$html .= '<div class="input-group input-group-sm m-t-5 sms-receiver display-flex">';
				$html .= '<input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="'.$language_resource['email'].'">';
				$html .= '</div>';
				$html .= '</div>';
			}
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		// ედიტის განრიგი ჩასასწორებელი
		$html .= '<div id="step-4" class="">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-4 padding"> ';
		$html .= '<div class="col-md-6">';
		$html .= '<select class="form-control" name="alert_date_from">';
		if(isset($alert->time->timefrom)) {
			$html .= '<option value="'.$alert -> time -> timefrom.'">'.substr($alert -> time -> timefrom,0,5).'</option>';
			$start = 0;
			$end   = 23;
			for($i = $start; $i <= $end; $i++)
			{   
				if($i<10){
					$hour = '0'.$i;
				}else{
					$hour = $i;
				}
				if($hour != substr($alert -> time -> timefrom,0,2))
				{
					$html .= '<option value="'.$hour.':00:00">'.$hour.':00</option>';
				}                                                                               
			}
		} else {
			$html .= "<option></option>";
			$start = 0;
			$end   = 23;
			for($i = $start; $i <= $end; $i++)
			{   
				if($i<10){
					$hour = '0'.$i;
				}else{
					$hour = $i;
				}         
				$html .= '<option value="'.$hour.':00:00">'.$hour.':00</option>';
			}
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<select class="form-control" name="alert_date_to">';
		if(isset($alert->time->timeto)) {
			$html .= '<option value="'.$alert -> time -> timeto.'">'.substr($alert -> time -> timeto,0,5).'</option>';
			$start = 0;
			$end   = 23;
			for($i = $start; $i <= $end; $i++)
			{   
				if($i<10){
					$hour = '0'.$i;
				}else{
					$hour = $i;
				}
				if(isset($alert->time->timeto)) {
					if($hour != substr($alert -> time -> timeto,0,2))
					{
						$html .= '<option value="'.$hour.':00:00">'.$hour.':00</option>';
					}
				}
			}
		} else {
			$html .= '<option></option>';
			$start = 0;
			$end   = 23;
			for($i = $start; $i <= $end; $i++)
			{   
				if($i<10){
					$hour = '0'.$i;
				}else{
					$hour = $i;
				}
				$html .= '<option value="'.$hour.':00:00">'.$hour.':00</option>';
			}
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-8">';
		$html .= '<div class="weekDays-selector">';

		if(isset($alert -> time -> weekdays)) {
			$weekdays_array = array_reverse(str_split(decbin($alert -> time -> weekdays)));
			foreach($weekdays_array as $key => $value)
			{   
				if($key == 0){$day_key  = 'mon';$day_name = $language_resource['mond'];}
				elseif ($key == 1) {$day_key  = 'tue';$day_name = $language_resource['thud'] ;}
				elseif ($key == 2) {$day_key  = 'wed';$day_name = $language_resource['wedd'];}
				elseif ($key == 3) {$day_key  = 'thu';$day_name = $language_resource['thed'];}
				elseif ($key == 4) {$day_key  = 'fri';$day_name = $language_resource['fryd'];}
				elseif ($key == 5) {$day_key  = 'sat';$day_name = $language_resource['sutd'];}
				elseif ($key == 6) {$day_key  = 'sun';$day_name = $language_resource['sund'];}
				if($value == 1) {$checker = 'checked';}else{$checker = '';}
				$html .= '<input type="checkbox" id="edit_weekday-'.$day_key.'" class="weekday2" name="edit_weekday['.$day_key.']" '.$checker.'/>';
				$html .= '<label for="edit_weekday-'.$day_key.'" >'.$day_name.'</label>';
			}
		} else {
			$html .= '<input type="checkbox" id="edit_weekday-mon" class="weekday" name="edit_weekday[mon]" checked/>';
			$html .= '<label for="edit_weekday-mon">'.$language_resource['mond'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-tue" class="weekday" name="edit_weekday[tue]" checked/>';
			$html .= '<label for="edit_weekday-tue">'.$language_resource['thud'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-wed" class="weekday" name="edit_weekday[wed]" checked/>';
			$html .= '<label for="edit_weekday-wed">'.$language_resource['wedd'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-thu" class="weekday" name="edit_weekday[thu]" checked/>';
			$html .= '<label for="edit_weekday-thu">'.$language_resource['thed'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-fri" class="weekday" name="edit_weekday[fri]" checked/>';
			$html .= '<label for="edit_weekday-fri">'.$language_resource['fryd'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-sat" class="weekday" name="edit_weekday[sat]" checked/>';
			$html .= '<label for="edit_weekday-sat">'.$language_resource['sutd'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-sun" class="weekday" name="edit_weekday[sun]" checked/>';
			$html .= '<label for="edit_weekday-sun">'.$language_resource['sund'].'</label>';
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		// მომხმარებლები
		$html .= '<div id="step-5" class="">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-12">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['users'].'</label>';
		$html .= '<div class="form-check">';
		$html .= '<input type="checkbox" class="form-check-input edit-user" id="edit_user_check_all"/>';
		$html .= '<label for="edit_user_check_all">ყველა</label>';
		$html .= '</div>';
		foreach($employers as $employer) {
			$html .= '<div class="form-check">';
			$html .= '<input type="checkbox" class="form-check-input edit-user" id="edit_user_'.$employer -> nickname.'" name="employers['.$employer -> nickname.']" />';
			$html .= '<label for="edit_user_'.$employer -> nickname.'">'.$employer -> nickname.'</label>';
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</form>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</td>';
		$html .= '<td class="td_buttons">';
		$html .= '<button class="btn btn-danger" data-toggle="modal" data-target="#delete_alert_'.$alert -> id.'"><span class="glyphicon glyphicon-trash"></span></button>';
		$html .= '<div class="modal fade" id="delete_alert_'.$alert -> id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
		$html .= '<div class="modal-dialog" role="document">';
		$html .= '<div class="modal-content">';
		$html .= '<div class="modal-header">';
		$html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		$html .= '<h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4>';
		$html .= '</div>';
		$html .= '<div class="modal-body">';
		$html .= '<p>'.$language_resource['deleteconf'].'?</p>';
		$html .= '<button class="btn btn-danger confirm-delete-alert" data-alert="'.$alert -> id.'"><span class="glyphicon glyphicon-trash"></span></button>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</td>';
		$html .= '</tr>';

		return response()->json([
			'success'       => 'ok',
			'html'          => $html
		]);
	}

	public function postEditAlert(Request $request)
	{
	    $user                 = Auth::user();
		$lang                 = Auth::user()->language;
		$language_resource    = Resource::getResourceForLanguage($lang);
	    $alert_id 			  = $request -> alert_id;
	    $alert_type_id        = 11; //Temperature alert type from documentation: https://casatrade.atlassian.net/wiki/spaces/GPSCON/pages/249921544/Alerts
	    $firmaid              = $user -> firmaid;
	    $alert_name           = $request -> alert_name;
	    $alert_limit          = $request -> alert_limit;
	    $alert_type           = $request -> alert_type;
	    $alert_time           = $request -> alert_time;
		if(isset($request -> alert_sensor)) {
			$alert_device =  $request -> alert_device;
			$alert_sensor = $request -> alert_sensor;
		} else {
			$alert_device = $request -> alert_imei_old;
			$alert_sensor = $request -> alert_index_old;
		}
		$alert_sound          = (isset($request -> alert_sound)) ? $request -> alert_sound : false;
	    $alert_popup          = (isset($request -> alert_popup)) ? $request -> alert_popup : false;
	    $alert_sms_standard   = $request -> alert_default_sms_standard;
	    $alert_sms_text       = $request -> alert_sms_text;
	    $alert_phones         = $request -> alert_phones;
	    $alert_emails         = $request -> alert_emails;
	    $alert_email_standard = $request -> alert_default_email_standard;
	    $alert_email_text     = $request -> alert_email_text;
	    $alert_date_from      = $request -> alert_date_from;
	    $alert_date_to        = $request -> alert_date_to;
	    $weekday              = $request -> edit_weekday;
	    $employers            = $request -> employers;

	    $weekdays = Functions::getBinaryFromWeekdays($weekday);

	    $decimal_weekdays = bindec($weekdays);

	    if($alert_sms_standard == false)
	    {
	      $defaultsms = $alert_sms_text;
	    }else{
	      $defaultsms = null;
	    }

	    if($alert_email_standard == false)
	    {
	      $defaultemail = $alert_email_text;
	    }else{
	      $defaultemail = null;
	    }

	    $alert = Alert::where('id', $alert_id)->first();

	    $alert -> update([
	      'alertstypeid' => $alert_type_id,
	      'firmaid'      => $firmaid,
	      'name'         => $alert_name,
	      'value1'       => $alert_type,
	      'value2'       => $alert_limit,
	      'value3'       => $alert_time,
	      'value4'       => $alert_sensor,
	      'allusers'     => true,
	      'sound'        => $alert_sound,
	      'popup'        => $alert_popup,
	      'defaultsms'   => $defaultsms,
	      'defaultemail' => $defaultemail
	    ]);

	    $alerts_to_device = Alert2Device::where('alertsid',$alert_id)->first();

	    $alerts_to_device -> update([
	      'imei'      => $alert_device
	    ]);

	    $alerts_to_user = Alert2User::where('alertsid',$alert_id)->delete();
	    
		if(!is_null($employers)) {
			foreach($employers as $username => $employer)
			{
			$alerts_to_user = Alert2User::create([
				'alertsid'  => $alert_id,
				'username'  => $username
			]);
			}
		}
	    
	    $alert_to_time_slot = Alert2TimeSlot::where('alertsid',$alert_id)->first();

		if(isset($alert_to_time_slot)) {
			$alert_to_time_slot -> update([
				'timefrom'  => $alert_date_from,
				'timeto'    => $alert_date_to,
				'weekdays'  => $decimal_weekdays
			  ]);
		}
	    
	    $alert_to_sms = Alert2Sms::where('alertsid',$alert_id)->delete();
		
	    for($i = 0; $i < sizeof($alert_phones['code']); $i++)
	    {
			if(!is_null($alert_phones['number'][$i])) {
				$alert_to_sms  = Alert2Sms::create([
					'alertsid'  => $alert -> id,
					'telephon'  => $alert_phones['code'][$i] . $alert_phones['number'][$i]
				]);
			}
	    }

	    $alerts_to_email = Alert2Email::where('alertsid',$alert_id)->delete();

		if(!is_null($alert_emails)) {
			foreach($alert_emails as $alert_email)
			{
				if(!is_null($alert_email)) {
					$alerts_to_email = Alert2Email::create([
						'alertsid'  => $alert -> id,
						'email'     => $alert_email
					]);
				}
			}
		}

	    $client = new SoapClient("http://10.79.79.32:4141/wsdlServices?wsdl");
		$client->command("#UPDATEALERT>" . $alert->id);

		$warehouses = Warehouse::where('firmaid', $user->firmaid)->get();
		$employers = User::where('firmaid',$user -> firmaid) -> get();
	    
		$html  = '';
		
		$html .= '<td>'.$alert -> name.'</td>';
		$html .= '<td class="center">'.$alert -> alert_type -> description.'</td>';
		$html .= '<td class="center">'.$alert -> device -> device -> name.'</td>';
		$html .= '<td class="center">'.$alert -> users -> count().'</td>';
		$html .= ($alert -> sound == 1) ? '<td class="center">'.$language_resource['yes'].'</td>' : '<td class="center">'.$language_resource['no'].'</td>';
		$html .= ($alert -> popup == 1) ? '<td class="center">'.$language_resource['yes'].'</td>' : '<td class="center">'.$language_resource['no'].'</td>';
		$html .= '<td class="td_buttons">';
		$html .= '<button class="btn btn-primary edit_alert" id="'.$alert->id.'" data-toggle="modal" data-target="#edit_alert_'.$alert->id.'">'.$language_resource['edit'].'</button>';
		$html .= '<div class="modal fade" id="edit_alert_'.$alert->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
		$html .= '<div class="modal-dialog modal-lg modal-dialog-centered" role="document">';
		$html .= '<div class="modal-content">';
		$html .= '<div class="modal-header">';
		$html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		$html .= '<h4 class="modal-title" id="myModalLabel">'.$language_resource['edit'].'</h4>';
		$html .= '</div>';
		$html .= '<div class="modal-body">';
		$html .= '<form class="edit_warehouse_room_sensor_alert_form" id="edit_warehouse_room_sensor_alert_form_'.$alert->id.'" method="POST" class="alerts_target">';
		$html .= '<input type="hidden" name="alert_id" value="'.$alert->id.'">';
		$html .= '<div id="smartwizardedit" class="smartwizard smartwizardedit" data-alert-id="'.$alert->id.'">';
		$html .= '<ul>';
		$html .= '<li><a href="#step-1"><small>'.$language_resource['configuration'].'</small></a></li>';
		$html .= '<li><a href="#step-2"><small>'.$language_resource['devices'].'</small></a></li>';
		$html .= '<li><a href="#step-3"><small>'.$language_resource['notifications'].'</small></a></li>';
		$html .= '<li><a href="#step-4"><small>'.$language_resource['schedule'].'</small></a></li>';
		$html .= '<li><a href="#step-5"><small>'.$language_resource['users'].'</small></a></li>';
		$html .= '</ul>';
		$html .= '<div>';
		// კონფიგურაცია
		$html .= '<div id="step-1">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-6"> ';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['desname'].'</label>';
		$html .= '<input type="text" class="form-control" name="alert_name" value="'.$alert -> name .'" required> ';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['alarm'].'</label>';
		$html .= '<select class="form-control" name="alert_type">';
		$html .= ($alert -> value3 == 1) ? '<option value="1">'.$language_resource['on-temp-rise'].'</option><option value="1">'.$language_resource['on-temp-decrease'].'</option>' : '<option value="0">'.$language_resource['on-temp-decrease'].'</option><option value="1">'.$language_resource['on-temp-rise'].'</option>';
		$html .= '</select>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="row mt-3">';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['bound'].' (°C):</label>';
		$html .= '<input type="number" class="form-control" name="alert_limit" required value="'.$alert -> value2.'"> ';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['exceeding-allowed-time'].' ('.$language_resource['minute'].'):</label>';
		$html .= '<input type="number" class="form-control" name="alert_time" min="1" step="1" max="60" required value="'.$alert -> value3.'"> ';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		// მოწყობილობა
		$html .= '<div id="step-2">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-12">';
		$html .= '<div class="form-group">';
		$html .= $alert -> sensor['controller']['warehouse']['name'] . ' - ' . $alert -> device -> name . ' - ' .  $alert -> sensor['name'];
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['warehouse'].'</label>';
		$html .= '<select class="form-control alerts_warehouse" name="alert_warehouse" id="warehouse_list_edit" data-alert-id="'.$alert -> id.'">';
		$html .= '<option value="0">'.$language_resource['warehouse_select'].'</option>';
		foreach ($warehouses as $warehouse) {
			$html .= '<option value="'.$warehouse -> id.'">'.$warehouse -> name.'</option>';
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['device'].'</label>';
		$html .= '<select class="form-control alerts_device" name="alert_device" id="device_list_'.$alert->id.'" data-alert-id="'.$alert->id.'" disabled>';
		$html .= '<option value="0">'.$language_resource['selectvehicles'].'</option>';
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['sensor'].'</label>';
		$html .= '<select class="form-control alerts_device" name="alert_sensor" id="sensor_list_'.$alert->id.'" disabled>';
		$html .= '<option value="0">'.$language_resource['choose-sensor'].'</option>';
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<span class="text-danger">*თუ გსურთ რომ განგაში გავრცელდეს მოწყობილობაზე მიბმული ყველა სენსორისათვის, სენსორების ასარჩევი ველი დატოვეთ შეუვსებელი</span>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		// შეტყობინებები
		$html .= '<div id="step-3" class="">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-12"> ';
		$html .= '<div class="form-group">';
		$html .= '<div class="col-md-12">  ';
		$html .= '<label>'.$language_resource['alert-accompaniment'].': </label>';
		$html .= '<div class="col-md-12 padding">';
		$html .= '<label class="checkbox-inline m-r-20">';
		$html .= ($alert -> sound == 1) ? '<input type="checkbox" name="alert_sound" value="true" checked>' . $language_resource['sound_signal'] : '<input type="checkbox" name="alert_sound" value="true">' . $language_resource['sound_signal'];
		$html .= '</label>';
		$html .= '<label class="checkbox-inline">';
		$html .= ($alert -> popup == 1) ? '<input type="checkbox" name="alert_popup" value="true" checked>' . $language_resource['popup-window'] : '<input type="checkbox" name="alert_popup" value="true">' . $language_resource['popup-window'];
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>SMS:</label>';
		$html .= '<div class="col-md-12 padding margin">';
		$html .= '<label>';
		$html .= '<small>';
		$html .= ($alert -> defaultsms == null) ? '<input type="radio" name="alert_default_sms_standard" class="m-r-5" value="true" checked="">' . $language_resource['standard'] : '<input type="radio" name="alert_default_sms_standard" class="m-r-5" value="false">' . $language_resource['standard'];
		$html .= '</small>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '<div class="col-md-12 padding margin">';
		$html .= '<label>';
		$html .= '<small>';
		$html .= ($alert -> defaultsms != null) ? '<input type="radio" name="alert_default_sms_standard" class="m-r-5 sms_radio" value="true" checked="">' . $language_resource['template'] : '<input type="radio" name="alert_default_sms_standard" class="m-r-5 sms_radio" value="false">' . $language_resource['template'];
		$html .= '</small>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-group sms_textarea_form_group">';
		$html .= '<div class="col-md-12 padding">';
		$html .= '<label><i class="fa fa-wrench"></i> '.$language_resource['constructor'].'</label>';
		$html .= '<textarea class="form-control" rows="2" name="alert_sms_text">';
		$html .= ($alert -> defaultsms != null) ? $alert -> defaultsms : '';
		$html .= '</textarea>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['sms-recipients'].':</label>';
		foreach($alert -> phones as $phone) {
			$html .= '<div class="col-md-12 padding margin">';
			$html .= '<div class="input-group input-group-sm m-t-5 sms-receiver display-flex">';
			$html .= '<select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">';
			$html .= (substr($phone -> telephon,0,3) == '995') ? '<option value="995">+995</option><option value="374">+374</option>' : '<option value="374">+374</option><option value="995">+995</option>';
			$html .= '</select>';
			$html .= '<input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX" value="'.substr($phone -> telephon,3).'">';
			$html .= '<div class="input-group-btn"></div>';
			$html .= '</div>';
			$html .= '</div>';
		}
		if($alert -> phones -> count() < 3) {
			for($i = $alert -> phones -> count(); $i < 3; $i++) {
				$html .= '<div class="col-md-12 padding margin">';
				$html .= '<div class="input-group input-group-sm m-t-5 sms-receiver display-flex">';
				$html .= '<select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">';
				$html .= '<option value="995">+995</option>';
				$html .= '<option value="374">+374</option>';
				$html .= '<input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX">';
				$html .= '<div class="input-group-btn"></div>';
				$html .= '</div>';
				$html .= '</div>';
			}
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['email'].':</label>';
		$html .= '<div class="col-md-12 padding margin">';
		$html .= '<label>';
		$html .= '<small>';
		$html .= ($alert -> defaultemail == null) ? '<input type="radio" name="alert_default_email_standard" class="m-r-5" value="true" checked="">' . $language_resource['standard'] : '<input type="radio" name="alert_default_email_standard" class="m-r-5" value="false">' . $language_resource['standard'];
		$html .= '</small>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '<div class="col-md-12 padding margin">';
		$html .= '<label>';
		$html .= '<small>';
		$html .= ($alert -> defaultemail != null) ? '<input type="radio" name="alert_default_email_standard" class="m-r-5 email_radio" value="true" checked="">' . $language_resource['template'] : '<input type="radio" name="alert_default_email_standard" class="m-r-5 email_radio" value="false">' . $language_resource['template'];
		$html .= '</small>';
		$html .= '</label>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-group email_textarea_form_group">';
		$html .= '<div class="col-md-12 padding">';
		$html .= '<label><i class="fa fa-wrench"></i> '.$language_resource['constructor'].'</label>';
		$html .= '<textarea class="form-control" rows="2" name="alert_email_text">';
		$html .= ($alert -> defaultemail != null) ? $alert -> defaultemail : '';
		$html .= '</textarea>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['email-recipients'].':</label>';
		foreach($alert -> emails as $email) {
			$html .= '<div class="col-md-12 padding margin">';
			$html .= '<div class="input-group input-group-sm m-t-5 sms-receiver display-flex">';
			$html .= '<input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="'.$language_resource['email'].'" value="'.$email -> email.'">';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '';
			$html .= '';
			$html .= '';
			$html .= '';
		}
		if($alert -> emails -> count() < 3) {
			for($i = $alert -> emails -> count(); $i < 3; $i++) {
				$html .= '<div class="col-md-12 padding margin">';
				$html .= '<div class="input-group input-group-sm m-t-5 sms-receiver display-flex">';
				$html .= '<input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="'.$language_resource['email'].'">';
				$html .= '</div>';
				$html .= '</div>';
			}
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		// ედიტის განრიგი ჩასასწორებელი
		$html .= '<div id="step-4" class="">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-4 padding"> ';
		$html .= '<div class="col-md-6">';
		$html .= '<select class="form-control" name="alert_date_from">';
		if(isset($alert->time->timefrom)) {
			$html .= '<option value="'.$alert -> time -> timefrom.'">'.substr($alert -> time -> timefrom,0,5).'</option>';
			$start = 0;
			$end   = 23;
			for($i = $start; $i <= $end; $i++)
			{   
				if($i<10){
					$hour = '0'.$i;
				}else{
					$hour = $i;
				}
				if($hour != substr($alert -> time -> timefrom,0,2))
				{
					$html .= '<option value="'.$hour.':00:00">'.$hour.':00</option>';
				}                                                                               
			}
		} else {
			$html .= "<option></option>";
			$start = 0;
			$end   = 23;
			for($i = $start; $i <= $end; $i++)
			{   
				if($i<10){
					$hour = '0'.$i;
				}else{
					$hour = $i;
				}         
				$html .= '<option value="'.$hour.':00:00">'.$hour.':00</option>';
			}
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '<div class="col-md-6">';
		$html .= '<select class="form-control" name="alert_date_to">';
		if(isset($alert->time->timeto)) {
			$html .= '<option value="'.$alert -> time -> timeto.'">'.substr($alert -> time -> timeto,0,5).'</option>';
			$start = 0;
			$end   = 23;
			for($i = $start; $i <= $end; $i++)
			{   
				if($i<10){
					$hour = '0'.$i;
				}else{
					$hour = $i;
				}
				if(isset($alert->time->timeto)) {
					if($hour != substr($alert -> time -> timeto,0,2))
					{
						$html .= '<option value="'.$hour.':00:00">'.$hour.':00</option>';
					}
				}
			}
		} else {
			$html .= '<option></option>';
			$start = 0;
			$end   = 23;
			for($i = $start; $i <= $end; $i++)
			{   
				if($i<10){
					$hour = '0'.$i;
				}else{
					$hour = $i;
				}
				$html .= '<option value="'.$hour.':00:00">'.$hour.':00</option>';
			}
		}
		$html .= '</select>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-md-8">';
		$html .= '<div class="weekDays-selector">';

		if(isset($alert -> time -> weekdays)) {
			$weekdays_array = array_reverse(str_split(decbin($alert -> time -> weekdays)));
			foreach($weekdays_array as $key => $value)
			{   
				if($key == 0){$day_key  = 'mon';$day_name = $language_resource['mond'];}
				elseif ($key == 1) {$day_key  = 'tue';$day_name = $language_resource['thud'] ;}
				elseif ($key == 2) {$day_key  = 'wed';$day_name = $language_resource['wedd'];}
				elseif ($key == 3) {$day_key  = 'thu';$day_name = $language_resource['thed'];}
				elseif ($key == 4) {$day_key  = 'fri';$day_name = $language_resource['fryd'];}
				elseif ($key == 5) {$day_key  = 'sat';$day_name = $language_resource['sutd'];}
				elseif ($key == 6) {$day_key  = 'sun';$day_name = $language_resource['sund'];}
				if($value == 1) {$checker = 'checked';}else{$checker = '';}
				$html .= '<input type="checkbox" id="edit_weekday-'.$day_key.'" class="weekday2" name="edit_weekday['.$day_key.']" '.$checker.'/>';
				$html .= '<label for="edit_weekday-'.$day_key.'" >'.$day_name.'</label>';
			}
		} else {
			$html .= '<input type="checkbox" id="edit_weekday-mon" class="weekday" name="edit_weekday[mon]" checked/>';
			$html .= '<label for="edit_weekday-mon">'.$language_resource['mond'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-tue" class="weekday" name="edit_weekday[tue]" checked/>';
			$html .= '<label for="edit_weekday-tue">'.$language_resource['thud'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-wed" class="weekday" name="edit_weekday[wed]" checked/>';
			$html .= '<label for="edit_weekday-wed">'.$language_resource['wedd'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-thu" class="weekday" name="edit_weekday[thu]" checked/>';
			$html .= '<label for="edit_weekday-thu">'.$language_resource['thed'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-fri" class="weekday" name="edit_weekday[fri]" checked/>';
			$html .= '<label for="edit_weekday-fri">'.$language_resource['fryd'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-sat" class="weekday" name="edit_weekday[sat]" checked/>';
			$html .= '<label for="edit_weekday-sat">'.$language_resource['sutd'].'</label>';
			$html .= '<input type="checkbox" id="edit_weekday-sun" class="weekday" name="edit_weekday[sun]" checked/>';
			$html .= '<label for="edit_weekday-sun">'.$language_resource['sund'].'</label>';
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		// მომხმარებლები
		$html .= '<div id="step-5" class="">';
		$html .= '<div class="row">';
		$html .= '<div class="col-md-12">';
		$html .= '<div class="form-group">';
		$html .= '<label>'.$language_resource['users'].'</label>';
		$html .= '<div class="form-check">';
		$html .= '<input type="checkbox" class="form-check-input edit-user" id="edit_user_check_all"/>';
		$html .= '<label for="edit_user_check_all">ყველა</label>';
		$html .= '</div>';
		foreach($employers as $employer) {
			$html .= '<div class="form-check">';
			$html .= '<input type="checkbox" class="form-check-input edit-user" id="edit_user_'.$employer -> nickname.'" name="employers['.$employer -> nickname.']" />';
			$html .= '<label for="edit_user_'.$employer -> nickname.'">'.$employer -> nickname.'</label>';
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</form>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</td>';
		$html .= '<td class="td_buttons">';
		$html .= '<button class="btn btn-danger" data-toggle="modal" data-target="#delete_alert_'.$alert -> id.'"><span class="glyphicon glyphicon-trash"></span></button>';
		$html .= '<div class="modal fade" id="delete_alert_'.$alert -> id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
		$html .= '<div class="modal-dialog" role="document">';
		$html .= '<div class="modal-content">';
		$html .= '<div class="modal-header">';
		$html .= '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
		$html .= '<h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4>';
		$html .= '</div>';
		$html .= '<div class="modal-body">';
		$html .= '<p>'.$language_resource['deleteconf'].'?</p>';
		$html .= '<button class="btn btn-danger confirm-delete-alert" data-alert="'.$alert -> id.'"><span class="glyphicon glyphicon-trash"></span></button>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';
		$html .= '</td>';


		return response()->json([
			'success'       => 'ok',
			'html'          => $html,
			'alertid'       => $alert -> id,
            'message'              => 'წარმატებით შეიცვალა',
            'alertType'            => 1
		]);
	}

	public function getDeleteAlert($alert_id)
	{

		$alert = Alert::where('id',$alert_id) -> delete();

		$client = new SoapClient("http://10.79.79.32:4141/wsdlServices?wsdl");
		$client->command("#UPDATEALERT>" . $alert_id);

		return response()->json([
			'success'       => 'ok'
		]);

	}


  	public function getRecivedAlerts()
  	{ 
	    $user   = Auth::user();
		$language_resource = Resource::getResourceForLanguage($user->language);

		$alerts = Alert::where('firmaid', $user->firmaid)->orderby('id', 'ASC')->get();

		foreach($alerts as $alert) {			
			foreach($alert -> getNonReadedAndTodayAllerts as $alertdata) {
				$alertdata -> sensor -> controller -> warehouse -> warehouse_rooms;
				$alertdata -> sensor -> roomToSensor -> room;
			}
		}

		return response()->json([
			'success'        	  =>'ok',
			'alerts'      	      => $alerts,
			'language_resource'   => $language_resource
		]);
  	}

  	public function getChangeAlertStatus(Request $request)
  	{

  		$alert_recived = AlertsRecived::where('id',$request -> alertid)->first();

  		$alert_recived -> update([
	      'readed' => true
	    ]);

  		return response()->json([
	      'success'   	=>'ok'
	    ]);
  	}

}
