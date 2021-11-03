<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use App\Warehouse;
use App\WarehouseRoom;
use App\WarehouseRoomSensor;
use DB;
use App\Device;
use App\Sensor2Device;
use App\Temperature;
use App\Firma;
use App\Transmission;
use App\TransmissionBack;
use Illuminate\Filesystem\Filesystem;
use App\Report;
use App\UserAction;
use App\Alert;
use App\Alert2Device;
use App\Alert2User;
use App\Alert2TimeSlot;
use App\Alert2Sms;
use App\Alert2Email;
use App\Resource;
use App\Sensor;
use App\SensorsController;
use App\DbAudit;
use App\Http\Controllers\Auth\LoginController;
use App\Helpers\Functions;

class PageController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    // LoginController::authenticated();
    return view('monitoring');

  }

  public function getMonitoring(Request $request)
  {
   	$user  = Auth::user();

    $language_resource = Resource::getResourceForLanguage($user->language);

    $warehouses = Warehouse::where([
      ['firmaid','=', $user -> firmaid],
      ['isactive', '=', true]
    ])->orderBy('id','ASC')->get();

    foreach($warehouses as $warehouse) {
      foreach($warehouse -> warehouse_rooms as $room) {
        $avgTemp = 0;
        $avgHumi = 0;
        $sensorsNumTempo = count($room -> sensors);
        $sensorsNumHumidity = count($room -> sensors);
        foreach($room -> sensors as $sensor) {
          if($sensor -> transmission) {
            if(!$sensor->isactive) {
              $sensorsNumTempo--;
              $sensorsNumHumidity--;
            } else {
              if($sensor -> transmission -> temperature_data_backs -> where('index', $sensor -> index)->first() -> tempo) {
                $avgTemp += $sensor -> transmission -> temperature_data_backs -> where('index', $sensor -> index)->first() -> tempo;
              } else {
                $sensorsNumTempo--;
              }
              if($sensor -> transmission -> temperature_data_backs -> where('index', $sensor -> index)->first() -> humidity) {
                $avgHumi += $sensor -> transmission -> temperature_data_backs -> where('index', $sensor -> index)->first() -> humidity;
              } else {
                $sensorsNumHumidity--;
              }
            }
          }
          $room['avgTempo'] = round($avgTemp/$sensorsNumTempo, 1);
          $room['avgHumi'] = round($avgHumi/$sensorsNumHumidity, 1);
        }
      }
    }

    if($request->json) {
      return response()->json([
        'success'             => 'ok',
        'warehouses'          => $warehouses,
        'language_resource'   => $language_resource
      ]);
    }

    return view('monitoring')->with([
      'active_menu_item'    => 'monitoring',
      'title'               => 'Monitoring',
      'user'                => $user,
      'warehouses'          => $warehouses,
      'language_resource'   => $language_resource
    ]);
  }

  public function getRoomSensors(Request $request)
  {
    $warehouse_room_sensors = Sensor::select('tempohub_sensors.id as id','tempohub_sensors.imei','tempohub_sensors.index','tempohub_sensors.isactive','tr.map_x','tr.map_y','tempohub_sensors.name as name','tr.roomid','tr.sensorid')->leftjoin('tempohub_roomsensors as tr', 'tr.sensorid', '=', 'tempohub_sensors.id')->where('tr.roomid', $request->roomid)->orderBy('tempohub_sensors.index', 'DESC')->get();

    return response()->json([
      'success' =>'ok',
      'sensors' => $warehouse_room_sensors
    ]);
  }

  public function getRoomLiveDataForMap(Request $request)
  {
    $user              = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    $sensors = WarehouseRoom::select('tempohub_rooms.id as roomid', 'tempohub_rooms.name as roomname', 'tempohub_rooms.image as image', 'tr.map_x', 'tr.map_y', 's.id as sensorid', 's.index as sensorindex', 's.name as sensorname', 's.imei as sensorimei', 's.isactive', 's.mintemp', 's.maxtemp', 's.minhum', 's.maxhum')->leftjoin('tempohub_roomsensors as tr', 'tr.roomid', '=', 'tempohub_rooms.id')->leftjoin('tempohub_sensors as s', 's.id', '=', 'tr.sensorid')->where('tempohub_rooms.id', $request->roomid)->get();

    foreach($sensors as $sensor) {
      $sensor['temperaturedata'] = Sensor::select('tm.transmissionid', 'tm.tempo', 'tm.humidity', 'tm.battery_proc')->leftjoin('transmission as t', 't.imei', '=', 'tempohub_sensors.imei')->leftjoin('temperaturedata as tm', 'tm.transmissionid','=','t.id')->where('tempohub_sensors.id', $sensor->sensorid)->where('tm.index', $sensor->sensorindex)->orderby('t.datestamploc', 'DESC')->first();
    }

    return response()->json([
      'success'             => 'ok',
      'sensors'             => $sensors,
      'language_resource'   => $language_resource,
    ]);
  }

  public function getRoomLiveData(Request $request)
  {
    $user  = Auth::user();
    $lang = $user->language;

    $host = "10.79.79.40";
    $conn = pg_pconnect("host=" . $host . " port=52461 dbname=gpscontrol user=gpscontrol password=fDc5DeF23SdadXdC457GbvS")
      or die("Connection failed" . pg_last_error());

    $array        = [];
    $sensors      = [];
    $live_sensors = [];
    $rooms_ar     = [];

    //6973766	354018117937627	7532	2021-07-07 14:14:07	41.7790366	44.7798833	ბოხუას ქუჩა 6, დიდუბე-ჩუღურეთის რაიონი, თბილისი	1	25.0	25.1	1	t

    $warehouses = Warehouse::where([
      ['firmaid','=', $user -> firmaid],
      ['isactive', '=', true]
    ])->orderBy('id','ASC')->get();

    foreach($warehouses as $warehouse)
    {
      foreach($warehouse -> warehouse_rooms as $warehouse_room)
      {
        $sql    = "SELECT a.id as warehouse_id,
                          a.name as warehouse_name, 
                          b.id as room_id, 
                          b.name as room_name,
                          e.id as sensorid, 
                          e.name as sensor_name,
                          e.index as sensor_index, 
                          d.id as transmissionid, 
                          d.datestamploc
                   FROM tempohub_warehouses a
                   JOIN tempohub_rooms b on a.id = b.warehouseid
                   JOIN tempohub_roomsensors c on b.id = c.roomid
                   JOIN tempohub_sensors e on c.sensorid = e.id
                   JOIN transmission d on e.imei = d.imei
                   WHERE a.firmaid = ".$user -> firmaid." AND a.id = ".$warehouse -> id." AND a.isactive = TRUE AND b.isactive = TRUE AND b.id = ".$warehouse_room -> id."
                   ORDER BY d.id DESC
                   LIMIT 1
                ";

        $result = pg_query($conn, $sql);

        while($row = pg_fetch_assoc($result)) {
          $sql = "SELECT tm.tempo, tm.humidity, tm.battery_proc, tr.id as transmissionid, s.id as sensorid, s.index as index FROM temperaturedata as tm left join transmission_back as tr on tr.id = tm.transmissionid left join tempohub_sensors as s on s.imei = tr.imei AND s.index = tm.index WHERE  transmissionid = ".$row['transmissionid']."";
          $result = pg_query($conn, $sql);
          while ($rowa = pg_fetch_assoc($result)) {
            $array[$rowa['sensorid']]['transmission_id'] = $row['transmissionid'];
            $array[$rowa['sensorid']]['date']            = $row['datestamploc'];
            $array[$rowa['sensorid']]['index']           = $rowa['index'];
            $array[$rowa['sensorid']]['tempo']           = $rowa['tempo'];
            $array[$rowa['sensorid']]['humidity']        = $rowa['humidity'];
            $array[$rowa['sensorid']]['battery_proc']    = $rowa['battery_proc'];
            $array[$rowa['sensorid']]['warehouse_id']    = $row['warehouse_id'];
            $array[$rowa['sensorid']]['warehouse_name']  = $row['warehouse_name'];
          }
        }
        array_push($rooms_ar,$warehouse_room -> id);
      }
    }
    array_push($test,  $array);
    foreach($rooms_ar as $room_id)
    {

      $warehouse_room_sensors        = WarehouseRoomSensor::select()->leftjoin('tempohub_sensors as s', 's.id', '=', 'sensorid')->where('roomid',$room_id)->get();
      $warehouse_room_sensors_active = WarehouseRoomSensor::select()->leftjoin('tempohub_sensors as s', 's.id', '=', 'sensorid')->where('roomid',$room_id)->where('s.isactive', true)->get();

      foreach($warehouse_room_sensors_active as $warehouse_room_sensor)
      {
        $key = $warehouse_room_sensor -> id;

        $sensors[$key]['sensorid']                   = $warehouse_room_sensor -> id;
        $sensors[$key]['sensor_name']                = $warehouse_room_sensor -> name;
        $sensors[$key]['temperaturedata_back_index'] = $warehouse_room_sensor -> index;
        $sensors[$key]['room_id']                    = $warehouse_room_sensor -> room -> id;
        $sensors[$key]['room_name']                  = $warehouse_room_sensor -> room -> name;
        $sensors[$key]['key']                        = $key;
      }

      $live_sensors[$room_id]['room_count_sensors']        = $warehouse_room_sensors -> count();
      $live_sensors[$room_id]['room_count_active_sensors'] = $warehouse_room_sensors_active -> count();
      $live_sensors[$room_id]['average_tempo']             = 20;
      $live_sensors[$room_id]['average_humidity']          = 20;
    }

    foreach($sensors as $sensor)
    {
      foreach($array as $k => $v)
      {
        if($sensor['key'] == $k)
        {
          $array[$k]['sensorid']      = $sensor['sensorid'];
          $array[$k]['sensor_name']   = $sensor['sensor_name'];
          $array[$k]['sensor_index']  = $sensor['temperaturedata_back_index'];
          $array[$k]['room_id']       = $sensor['room_id'];
          $array[$k]['room_name']     = $sensor['room_name'];
        }
      }
    }



    return response()->json([
      'success'       => 'ok',
      'live'          => $array,
      'live_sensors'  => $live_sensors,
      'lang'          => $lang,
      'test'          => $test
    ]);
  }

  public function getSensorPast7DaysData(Request $request)
  {
    $user              = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);
    $today      = Carbon::today()->toDateString();
    $past_7_day = Carbon::now()->subDays(7)->toDateString();

    $host = "10.79.79.40";

    $conn = pg_pconnect("host=" . $host . " port=52461 dbname=gpscontrol user=gpscontrol password=fDc5DeF23SdadXdC457GbvS")
      or die("Connection failed" . pg_last_error());

    $sensor = Sensor::where('id', $request->sensorid)->first();

    $sql = "SELECT date_trunc('minute', a.datestamploc) - (CAST(EXTRACT(MINUTE FROM a.datestamploc) AS integer) % 60) * interval '1 minute' AS trunc_60_minute,
    ROUND(sum(b.tempo)/count(*),1) as tempo_average, ROUND(sum(b.humidity)/count(*),1) as humidity_average
    FROM transmission_back a
    JOIN temperaturedata_back b on a.id = b.transmissionid
    where a.imei = ".$sensor -> imei." AND a.dateloc > '".$past_7_day."' AND a.dateloc <= '".$today."' AND b.index = ".$sensor -> index."
    GROUP BY trunc_60_minute
    ORDER BY trunc_60_minute";

    $result = pg_query($conn, $sql);

    $tempos = [];
    $humdts = [];

    while($row = pg_fetch_assoc($result)) {
      $array[$row['trunc_60_minute']]['date']       = $row['trunc_60_minute'];
      $array[$row['trunc_60_minute']]['tempo']      = $row['tempo_average'];
      $array[$row['trunc_60_minute']]['humidity']   = $row['humidity_average'];
      array_push($tempos,$row['tempo_average']);
      array_push($humdts,$row['humidity_average']);
    }

    // $array['max_tempo'] = max($tempos);
    // $array['min_tempo'] = min($tempos);
    // $array['ave_tempo'] = round(array_sum($tempos)/count($tempos),2);

    // $array['max_humidity'] = max($humdts);
    // $array['min_humidity'] = min($humdts);
    // $array['ave_humidity'] = round(array_sum($humdts)/count($humdts),2);

    return response()->json([
      'success'           => 'ok',
      'data'              => $array,
      'sensor'            => $sensor,
      'language_resource' => $language_resource
    ]);

  }

  public function getRoomAverageData(Request $request)
  {
    $host = "10.79.79.40";
    $conn = pg_pconnect("host=" . $host . " port=52461 dbname=gpscontrol user=gpscontrol password=fDc5DeF23SdadXdC457GbvS")
      or die("Connection failed" . pg_last_error());

    $firmaid = Auth::user()->firmaid;

    $room_id = $request -> roomid;

    $array = [];
    $indexes_ar = [];

    $warehouse_room = WarehouseRoom::where('id',$room_id)->first();

    $warehouse_id = $warehouse_room -> warehouseid;

    $warehouse_room_sensors = WarehouseRoomSensor::select()->leftjoin('tempohub_sensors as s', 's.id', '=', 'tempohub_roomsensors.sensorid')->where('roomid', $room_id)->where('s.isactive',true)->get();

    foreach($warehouse_room_sensors as $warehouse_room_sensor)
    {
      array_push($indexes_ar, $warehouse_room_sensor -> index);
    }

    $sensor_indexes = '(';
    foreach($indexes_ar as $value)
    {
      $sensor_indexes .= $value.',';
    }

    $sensor_indexes = substr($sensor_indexes, -0, -1);

    $sensor_indexes .= ')';

    $sql = "SELECT a.id as warehouse_id,a.name as warehouse_name, b.id as room_id, b.name as room_name,e.id as sensorid, e.name as sensor_name,e.index as sensor_index, d.id as transmission_id, d.datestamploc
            FROM tempohub_warehouses a
            JOIN tempohub_rooms b on a.id = b.warehouseid
            JOIN tempohub_roomsensors c on b.id = c.roomid
            JOIN tempohub_sensors e on c.sensorid = e.id
            JOIN transmission d on e.imei = d.imei
            WHERE a.firmaid = ".$firmaid." AND a.id = ".$warehouse_id." AND a.isactive = TRUE AND b.isactive = TRUE AND b.id = ".$room_id." AND e.isactive = TRUE
            ORDER BY d.id DESC";

    $result = pg_query($conn, $sql);

    while($row = pg_fetch_assoc($result)) {

      $sql    = "SELECT * FROM temperaturedata WHERE transmissionid = ".$row['transmission_id']." AND index IN ".$sensor_indexes." ";
      $result = pg_query($conn, $sql);

      while ($rowa = pg_fetch_assoc($result)) {
        $array[$rowa['index']]['tempo']           = $rowa['tempo'];
        $array[$rowa['index']]['humidity']        = $rowa['humidity'];
        $array[$rowa['index']]['battery_proc']    = $rowa['battery_proc'];
      }
    }

    $tempo_count    = 0;
    $tempo_sum      = 0;

    $humidity_count = 0;
    $humidity_sum   = 0;

    $battery_count  = 0;
    $battery_sum    = 0;

    foreach($array as $key => $value)
    {
      if($value['tempo'] != null){
        $tempo_count = $tempo_count +1;
        $tempo_sum   = $tempo_sum + $value['tempo'];
      }
      if($value['humidity'] != null){
        $humidity_count = $humidity_count +1;
        $humidity_sum   = $humidity_sum + $value['humidity'];
      }
      if($value['battery_proc'] != null){
        $battery_count = $battery_count +1;
        $battery_sum   = $battery_sum + $value['battery_proc'];
      }
    }

    $tempo_average    = round($tempo_sum / $tempo_count,1);
    $humidity_average = round($humidity_sum / $humidity_count,1);
    $battery_average  = round($battery_sum / $battery_count);

    $output_array[$room_id]['tempo_average']    = $tempo_average;
    $output_array[$room_id]['humidity_average'] = $humidity_average;
    $output_array[$room_id]['battery_average']  = $battery_average;

    return response()->json([
      'success'   => 'ok',
      'data'      => $output_array
    ]);
  }

  public function getAllSensorsTodayDataByRoomId(Request $request)
  {
    $lang = Auth::user()->language;

    $today      = Carbon::today()->toDateString();

    $host = "10.79.79.40";
    $conn = pg_pconnect("host=" . $host . " port=52461 dbname=gpscontrol user=gpscontrol password=fDc5DeF23SdadXdC457GbvS")
      or die("Connection failed" . pg_last_error());

    $warehouse_room_sensors = WarehouseRoomSensor::select()->leftjoin('tempohub_sensors as s', 's.id', '=', 'tempohub_roomsensors.sensorid')->where('tempohub_roomsensors.roomid',$request -> roomid)->whereNotNull('tempohub_roomsensors.sensorid')->get();

    $array = [];

    foreach($warehouse_room_sensors as $sensor)
    {
      $array[$sensor -> index] = $sensor -> imei;
    }

    $sensor_indexes = '(';
    foreach($array as $key => $value)
    {
      $sensor_imei = $value;
      $sensor_indexes .= $key.',';
    }

    $sensor_indexes = substr($sensor_indexes, -0, -1);

    $sensor_indexes .= ')';

    $sql = "SELECT date_trunc('minute', a.datestamploc) - (CAST(EXTRACT(MINUTE FROM a.datestamploc) AS integer) % 60) * interval '1 minute' AS trunc_60_minute,
    ROUND(sum(b.tempo)/count(*),2) as tempo_average, ROUND(sum(b.humidity)/count(*),2) as humidity_average
    FROM transmission_back a
    JOIN temperaturedata_back b on a.id = b.transmissionid
    where a.imei = ".$sensor_imei." AND a.dateloc = '".$today."'  AND b.index IN ".$sensor_indexes."
    GROUP BY trunc_60_minute
    ORDER BY trunc_60_minute";

    $result = pg_query($conn, $sql);

    $tempos = [];
    $humdts = [];

    while($row = pg_fetch_assoc($result)) {
      $array_today[$row['trunc_60_minute']]['date']       = $row['trunc_60_minute'];
      $array_today[$row['trunc_60_minute']]['tempo']      = $row['tempo_average'];
      $array_today[$row['trunc_60_minute']]['humidity']   = $row['humidity_average'];
      array_push($tempos,$row['tempo_average']);
      array_push($humdts,$row['humidity_average']);
    }

    $array_today['max_tempo'] = max($tempos);
    $array_today['min_tempo'] = min($tempos);
    $array_today['ave_tempo'] = round(array_sum($tempos)/count($tempos),2);

    $array_today['max_humidity'] = max($humdts);
    $array_today['min_humidity'] = min($humdts);
    $array_today['ave_humidity'] = round(array_sum($humdts)/count($humdts),2);

    return response()->json([
      'success'      => 'ok',
      'data'         => $array_today,
      'lang'         => $lang
    ]);
  }

  public function getReports(Request $request)
  {
    $user              = Auth::user();
    $langauge_resource = session()->get('langauge_resource');
    $warehouses        = Warehouse::where('firmaid', $user->firmaid)->get();
    $rooms = [];

    if(!$warehouses->isEmpty()) {
      $rooms = WarehouseRoom::where('warehouseid', $warehouses[0]->id)->get();
      foreach($rooms as $room) {
        $room -> sensors();
      }
    }

    $data_array = [];

    if(!empty($request->all())) {
      $host = "10.79.79.40";
      $conn = pg_pconnect("host=" . $host . " port=52461 dbname=gpscontrol user=gpscontrol password=fDc5DeF23SdadXdC457GbvS") or die("Connection failed" . pg_last_error());
      $timeinterval = $request->timeinterval * 60;
      $now = Carbon::now()->toDateTimeString();
      $yesterday = Carbon::yesterday();

      $startdate = (!empty($request->startdate)) ? $request->startdate : Carbon::parse($yesterday)->format('Y, m, d');
      $enddate = (!empty($request->enddate)) ? $request->enddate : Carbon::parse($now)->format('Y, m, d');



      if($request->reporttype == 'temperature') {
        $sql = "SELECT w.name as warehousename, r.name as roomname, s.name as sensorname, tbm.datestamploc as datestamploc, tbm.tempo as tempo, tbm.humidity as humidity, tbm.battery_proc as battery_proc from tempohub_sensors s
          left join tempohub_roomsensors tr on tr.sensorid = s.id
          left join (select * from transmission_back as tb
                     left join temperaturedata_back as tm on tm.transmissionid = tb.id
                     where tb.imei = '".$request->sensorimei."' AND tm.index = ".$request->sensorindex." 
                     AND tb.datestamploc > '".$startdate."' AND tb.datestamploc < '".$enddate."') as tbm on tbm.imei = s.imei AND tbm.index = s.index
          left join tempohub_rooms as r on r.id = tr.roomid
          left join tempohub_warehouses as w on w.id = r.warehouseid
          where s.isactive = 't' AND tr.sensorid IS NOT NULL
          AND w.id = ".$request->warehouseid." AND tr.roomid = ".$request->roomid." AND s.index = ".$request->sensorindex." AND s.imei = '".$request->sensorimei."'
          ORDER BY tbm.datestamploc ASC";

        $result = pg_query($conn, $sql);
        $row = pg_fetch_assoc($result);

        $tmp_data_array = [];
        while($row = pg_fetch_assoc($result)) {
          array_push($tmp_data_array, $row);
        }

        array_push($data_array, $tmp_data_array[0]);
        for($i = 0; $i < count($tmp_data_array); $i++) {
          $date = Carbon::parse(end($data_array)['datestamploc']);
          $date2 = Carbon::parse($tmp_data_array[$i]['datestamploc']);
          if($date->diffInSeconds($date2) > $timeinterval) {
            array_push($data_array, $tmp_data_array[$i]);
          }
        }
      } elseif ($request->reporttype == 'temp_range') {
        $sql = "SELECT tm.tempo, t.datestamploc from transmission_back as t
                left join temperaturedata_back as tm on tm.transmissionid = t.id
                where t.datestamploc >= '".$startdate."' AND t.datestamploc < '".$enddate."'
                AND t.imei = '".$request->sensorimei."' AND tm.index = ".$request->sensorindex."
                AND tm.tempo IS NOT NULL AND tm.tempo < 50
                ORDER BY t.datestamploc ASC";
        $result = pg_query($conn, $sql);
        $row = pg_fetch_assoc($result);

        $tmp_data_array = [];
        while($row = pg_fetch_assoc($result)) {
          array_push($tmp_data_array, $row);
        }

        $data_array[0]['max_tempo'] = max(array_column($tmp_data_array, 'tempo'));
        $data_array[0]['min_tempo'] = min(array_column($tmp_data_array, 'tempo'));
        $data_array[0]['avg_tempo'] = array_sum(array_column($tmp_data_array, 'tempo')) / count(array_column($tmp_data_array, 'tempo'));
        $data_array[0]['range1'] = 0;
        $data_array[0]['rangetime1'] = 0;
        $data_array[0]['range2'] = 0;
        $data_array[0]['rangetime2'] = 0;
        $data_array[0]['range3'] = 0;
        $data_array[0]['rangetime3'] = 0;
        $data_array[0]['range4'] = 0;
        $data_array[0]['rangetime4'] = 0;
        $data_array[0]['range5'] = 0;
        $data_array[0]['rangetime5'] = 0;
        $data_array[0]['full_count'] = count($tmp_data_array);

        for($i = 0; $i < $data_array[0]['full_count']; $i++) {
          $tmp_data_array[$i - 1]['datestamploc'] = (!isset($tmp_data_array[$i - 1]['datestamploc'])) ? $tmp_data_array[$i]['datestamploc'] : $tmp_data_array[$i - 1]['datestamploc'];

          if(!empty($request->start1) && !empty($request->end1)) {
            if($tmp_data_array[$i]['tempo'] >= $request->start1 && $tmp_data_array[$i]['tempo'] < $request->end1) {
              $data_array[0]['rangetime1'] += strtotime($tmp_data_array[$i]['datestamploc']) - strtotime($tmp_data_array[$i - 1]['datestamploc']);
              $data_array[0]['range1']++;
            }
          }
          if(!empty($request->start2) && !empty($request->end2)) {
            if($tmp_data_array[$i]['tempo'] >= $request->start2 && $tmp_data_array[$i]['tempo'] < $request->end2) {
              $data_array[0]['range2']++;
              $data_array[0]['rangetime2'] += strtotime($tmp_data_array[$i]['datestamploc']) - strtotime($tmp_data_array[$i - 1]['datestamploc']);
            }
          }
          if(!empty($request->start3) && !empty($request->end3)) {
            if($tmp_data_array[$i]['tempo'] >= $request->start3 && $tmp_data_array[$i]['tempo'] < $request->end3) {
              $data_array[0]['range3']++;
              $data_array[0]['rangetime3'] += strtotime($tmp_data_array[$i]['datestamploc']) - strtotime($tmp_data_array[$i - 1]['datestamploc']);
            }
          }
          if(!empty($request->start4) && !empty($request->end4)) {
            if($tmp_data_array[$i]['tempo'] >= $request->start4 && $tmp_data_array[$i]['tempo'] < $request->end4) {
              $data_array[0]['range4']++;
              $data_array[0]['rangetime4'] += strtotime($tmp_data_array[$i]['datestamploc']) - strtotime($tmp_data_array[$i - 1]['datestamploc']);
            }
          }
          if(!empty($request->start5) && !empty($request->end5)) {
            if($tmp_data_array[$i]['tempo'] >= $request->start5 && $tmp_data_array[$i]['tempo'] < $request->end5) {
              $data_array[0]['range5']++;
              $data_array[0]['rangetime5'] += strtotime($tmp_data_array[$i]['datestamploc']) - strtotime($tmp_data_array[$i - 1]['datestamploc']);
            }
          }
        }

        $data_array[0]['rangetime1readable'] =  ($data_array[0]['rangetime1'] > 0) ? Functions::time2readable($data_array[0]['rangetime1']) : $data_array[0]['rangetime1'];
        $data_array[0]['rangetime2readable'] =  ($data_array[0]['rangetime2'] > 0) ? Functions::time2readable($data_array[0]['rangetime2']) : $data_array[0]['rangetime2'];
        $data_array[0]['rangetime3readable'] =  ($data_array[0]['rangetime3'] > 0) ? Functions::time2readable($data_array[0]['rangetime3']) : $data_array[0]['rangetime3'];
        $data_array[0]['rangetime4readable'] =  ($data_array[0]['rangetime4'] > 0) ? Functions::time2readable($data_array[0]['rangetime4']) : $data_array[0]['rangetime4'];
        $data_array[0]['rangetime5readable'] =  ($data_array[0]['rangetime5'] > 0) ? Functions::time2readable($data_array[0]['rangetime5']) : $data_array[0]['rangetime5'];
        $data_array[0]['fulltime'] = Functions::time2readable($data_array[0]['rangetime1'] + $data_array[0]['rangetime2'] + $data_array[0]['rangetime3'] + $data_array[0]['rangetime4'] + $data_array[0]['rangetime5']);

        $data_array[0]['range1percent'] = $data_array[0]['range1'] * 100 / $data_array[0]['full_count'];
        $data_array[0]['range2percent'] = $data_array[0]['range2'] * 100 / $data_array[0]['full_count'];
        $data_array[0]['range3percent'] = $data_array[0]['range3'] * 100 / $data_array[0]['full_count'];
        $data_array[0]['range4percent'] = $data_array[0]['range4'] * 100 / $data_array[0]['full_count'];
        $data_array[0]['range5percent'] = $data_array[0]['range5'] * 100 / $data_array[0]['full_count'];

        $data_array[0]['fullpercent'] = $data_array[0]['range1percent'] + $data_array[0]['range2percent'] + $data_array[0]['range3percent'] + $data_array[0]['range4percent'] + $data_array[0]['range5percent'];

      }

      return response()->json([
        'success'              => 'ok',
        'data_array'           => $data_array,
        'sql'                  => $sql
      ]);
    } else {
      return view('reports')->with([
        'active_menu_item'     => 'reports',
        'user'                 =>  $user,
        'title'		     	       => 'Reports',
        'active_sub_menu_item' => 'general',
        'language_resource'    => $langauge_resource,
        'data_array'           => $data_array,
        'warehouses'           => $warehouses,
        'rooms'                => $rooms,
       ]);
    }
  }

  public function getReportsExport(Request $request)
  {
    $user              = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);
    $warehouses        = Warehouse::where('firmaid', $user->firmaid)->get();
    $rooms             = WarehouseRoom::where('warehouseid', $warehouses[0]->id)->get();
    $data_array = [];

    if(!empty($request->all())) {
      $host = "10.79.79.40";
      $conn = pg_pconnect("host=" . $host . " port=52461 dbname=gpscontrol user=gpscontrol password=fDc5DeF23SdadXdC457GbvS")
        or die("Connection failed" . pg_last_error());
      $timeinterval = $request->timeinterval * 60;

      $now = Carbon::now()->toDateTimeString();
      $yesterday = Carbon::yesterday();
      $startdate = (!empty($request->startdate)) ? $request->startdate : Carbon::parse($yesterday)->format('Y, m, d');
      $enddate = (!empty($request->enddate)) ? $request->enddate : Carbon::parse($now)->format('Y, m, d');


      $sql = "SELECT w.name as warehousename, r.name as roomname, s.name as sensorname, tb.datestamploc as datestamploc, tm.tempo as tempo, tm.humidity as humidity, tm.battery_proc as battery_proc from tempohub_sensors s
              left join tempohub_roomsensors tr on tr.sensorid = s.id 
              left join transmission_back tb on tb.imei = s.imei
              left join temperaturedata_back tm on tm.transmissionid = tb.id AND tm.index = s.index
              left join tempohub_rooms as r on r.id = tr.roomid
              left join tempohub_warehouses as w on w.id = r.warehouseid
              where s.isactive = 't' AND tr.sensorid IS NOT NULL
              AND tm.tempo > 0 AND tm.humidity IS NOT NULL
              AND tb.datestamploc >= '".$startdate."' AND tb.datestamploc <= '".$enddate."'
              AND w.id =  ".$request->warehouseid." AND tr.roomid = ".$request->roomid."
              ORDER BY tb.datestamploc ASC";

      $result = pg_query($conn, $sql);
      $row = pg_fetch_assoc($result);
      $tmp_data_array = [];
      while($row = pg_fetch_assoc($result)) {
        array_push($tmp_data_array, $row);
      }
      array_push($data_array, $tmp_data_array[0]);
      for($i = 0; $i < count($tmp_data_array); $i++) {
        $date = Carbon::parse(end($data_array)['datestamploc']);
        $date2 = Carbon::parse($tmp_data_array[$i]['datestamploc']);
        if($date->diffInSeconds($date2) > $timeinterval) {
          array_push($data_array, $tmp_data_array[$i]);
        }
      }
      return response()->json([
        'success'              => 'ok',
        'data_array'           => $data_array
      ]);
    } else {
      return view('reports')->with([
        'active_menu_item'     => 'reports',
        'user'                 =>  $user,
        'title'		     	       => 'Reports',
        'active_sub_menu_item' => 'general',
        'language_resource'    => $language_resource,
        'data_array'           => $data_array,
        'warehouses'           => $warehouses,
        'rooms'                => $rooms
       ]);
    }
  }

  public function getReportUserActions(Request $request)
  {
    $user     = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    $users    = User::where('firmaid', $user -> firmaid)->orderBy('nickname','DESC')->get();

    $nickname = $request -> input('user_nickname');

    $start_date = $request -> input('start_date');
    $end_date   = $request -> input('end_date');

    $user_actions = UserAction::where('username',$user -> nickname);

    if($nickname && $nickname !=0)
    {
      $user_actions = UserAction::where('nickname',$nickname);
    }

    if ($start_date)
    {
      $user_actions -> where('created_at', '>=', $start_date);
    }

    if ($end_date)
    {
      $user_actions -> where('created_at', '<=', $end_date);
    }

    $user_actions = $user_actions -> get();

    return view('report_user_actions')->with([
      'active_menu_item'      => 'reports',
      'active_sub_menu_item'  => 'user_actions',
      'title'                 => 'Reports',
      'user'                  => $user,
      'users'                 => $users,
      'user_actions'          => $user_actions,
      'language_resource'     => $language_resource
    ]);
  }

  public function getReportAlerts(Request $request)
  {
    $user     = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    return view('report_alerts')->with([
      'active_menu_item'      => 'reports',
      'active_sub_menu_item'  => 'alerts',
      'title'                 => 'Reports',
      'user'                  => $user,
      'language_resource'     => $language_resource
    ]);
  }

  public function getAlerts()
  {
    $user = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);
    $devices = Device::where([['firmaid', $user -> firmaid],['enable',true]]) -> get();

   	return view('alerts')->with([
			'active_menu_item' => 'alerts',
    	'title'		         => 'Alerts',
      'user'             => $user,
      'devices'          => $devices,
      'language_resource'     => $language_resource
   	]);
  }

  public function getSettings()
  {
    $user = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

   	return view('settings')->with([
			'active_menu_item'      => 'settings',
    	'title'		              => 'Settings',
      'user'                  => $user,
      'language_resource'     => $language_resource
   	]);
  }

  public function getAdministration()
  {
    $user = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);
    $warehouses = Warehouse::where('firmaid',$user -> firmaid)-> orderBy('id','DESC') -> get();

		return view('administration')->with([
			'active_menu_item'     => 'administration',
      'active_sub_menu_item' => 'warehouses',
    	'title'		             => 'Administration',
      'user'                 => $user,
      'warehouses'           => $warehouses,
      'language_resource'     => $language_resource
   	]);

  }

  public function getDeleteWarehouse($warehouse_id)
  {
    $warehouse = Warehouse::where('id',$warehouse_id)->delete();

    // User Action

    return response()->json([
      'success'        => 'ok',
      'message'        => 'საწყობი წარმატებით წაიშალა',
      'alertType'      => 1
    ]);
  }

  public function postAddWarehouse(Request $request)
  {
    $user = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);
    $warehouse = Warehouse::create([
      'name' => $request->input('name'),
      'isactive'         => $request->input('isactive'),
      'firmaid'        => $request->input('firmaid')
    ]);

    // User Action

    $isactive = $warehouse->isactive ? $language_resource['aktiv'] : $language_resource['inactive'];
    $html = '<tr data-warehouse-id="'.$warehouse->id.'">';
    $html .= '<td class="td_id">'.$warehouse->id.'</td>';
    $html .= '<td>'.$warehouse->name.'</td>';
    $html .= '<td class="td_id">'.$isactive.'</td>';
    $html .= '<td class="td_buttons"><a href="/administration/warehouse-rooms/'.$warehouse->id.'"><button class="btn btn-default"><span class="glyphicon glyphicon-th-large"></span></button></a></td>';
    $html .= '<td class="td_buttons">';
    $html .= '<button class="btn btn-primary" data-toggle="modal" data-target="#edit_warehouse_'.$warehouse->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
    $html .= '<div class="modal fade" id="edit_warehouse_'.$warehouse->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
    $html .= '<div class="modal-dialog" role="document">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['edit'].'</h4></div>';
    $html .= '<div class="modal-body">';
    $html .= '<input type="hidden" name="_token" value="'.csrf_token().'">';
    $html .= '<input type="hidden" name="firmaid" value="'.$user->firmaid.'">';
    $html .= '<input type="hidden" name="id" value="'.$warehouse->id.'">';
    $html .= '<div class="form-group"><label>'.$language_resource['desname'].'</label><input type="text" class="form-control" name="name" value="'.$warehouse->name.'"></div>';
    $html .= '<div class="form-group"><label>'.$language_resource['status'].'</label><select class="form-control" name="isactive">';
    $html .= $warehouse->isactive ? '<option value="1">'.$language_resource['aktiv'].'</option><option value="0">'.$language_resource['inactive'].'</option>' : '<option value="0">'.$language_resource['inactive'].'</option><option value="1">'.$language_resource['aktiv'].'</option>';
    $html .= '</select></div>';
    $html .= '<div class="form-group"><button class="btn btn-primary confirm-edit-warehouse" data-warehouse="'.$warehouse->id.'">'.$language_resource['edit'].'</button></div>';
    $html .= '</div></div></div></div></td>';
    $html .= '<td class="td_buttons">';
    $html .= '<button class="btn btn-danger" data-toggle="modal" data-target="#delete_warehouse_'.$warehouse->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
    $html .= '<div class="modal fade" id="delete_warehouse_'.$warehouse->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
    $html .= '<div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
    $html .= '<div class="modal-body"><p>'.$language_resource['deleteconf'].'?</p>';
    $html .= '<button class="btn btn-danger confirm-delete-warehouse" data-warehouse="'.$warehouse->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
    $html .= '</div></div></div></div></td></tr>';


    return response()->json([
      'success'              => 'ok',
      'html'                 => $html,
      'warehouse'            => $warehouse,
      'message'              => 'საწყობი წარმატებით დაემატა',
      'alertType'            => 1
    ]);
  }

  public function postEditWarehouse(Request $request)
  {
    $user = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);
    $warehouse = Warehouse::where('id', $request->input('id'))->first();
    $warehouse->update($request->all());

    // User Action

    $isactive = $warehouse->isactive ? $language_resource['aktiv'] : $language_resource['inactive'];
    $html = '<td class="td_id">'.$warehouse->id.'</td>';
    $html .= '<td>'.$warehouse->name.'</td>';
    $html .= '<td class="td_id">'.$isactive.'</td>';
    $html .= '<td class="td_buttons"><a href="/administration/warehouse-rooms/'.$warehouse->id.'"><button class="btn btn-default"><span class="glyphicon glyphicon-th-large"></span></button></a></td>';
    $html .= '<td class="td_buttons">';
    $html .= '<button class="btn btn-primary" data-toggle="modal" data-target="#edit_warehouse_'.$warehouse->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
    $html .= '<div class="modal fade" id="edit_warehouse_'.$warehouse->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
    $html .= '<div class="modal-dialog" role="document">';
    $html .= '<div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['edit'].'</h4></div>';
    $html .= '<div class="modal-body">';
    $html .= '<input type="hidden" name="_token" value="'.csrf_token().'">';
    $html .= '<input type="hidden" name="firmaid" value="'.$user->firmaid.'">';
    $html .= '<input type="hidden" name="id" value="'.$warehouse->id.'">';
    $html .= '<div class="form-group"><label>'.$language_resource['desname'].'</label><input type="text" class="form-control" name="name" value="'.$warehouse->name.'"></div>';
    $html .= '<div class="form-group"><label>'.$language_resource['status'].'</label><select class="form-control" name="isactive">';
    $html .= $warehouse->isactive ? '<option value="1">'.$language_resource['aktiv'].'</option><option value="0">'.$language_resource['inactive'].'</option>' : '<option value="0">'.$language_resource['inactive'].'</option><option value="1">'.$language_resource['aktiv'].'</option>';
    $html .= '</select></div>';
    $html .= '<div class="form-group"><button class="btn btn-primary confirm-edit-warehouse" data-warehouse="'.$warehouse->id.'">'.$language_resource['edit'].'</button></div>';
    $html .= '</div></div></div></div></td>';
    $html .= '<td class="td_buttons">';
    $html .= '<button class="btn btn-danger" data-toggle="modal" data-target="#delete_warehouse_'.$warehouse->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
    $html .= '<div class="modal fade" id="delete_warehouse_'.$warehouse->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">';
    $html .= '<div class="modal-dialog" role="document"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
    $html .= '<div class="modal-body"><p>'.$language_resource['deleteconf'].'?</p>';
    $html .= '<button class="btn btn-danger confirm-delete-warehouse" data-warehouse="'.$warehouse->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
    $html .= '</div></div></div></div></td>';

    return response()->json([
      'success'              => 'ok',
      'html'                 => $html,
      'warehouse'            => $warehouse,
      'message'              => 'საწყობი წარმატებით შეიცვალა',
      'alertType'            => 1
    ]);
  }

  public function getWarehouseRooms($warehouse_id)
  {
    $user              = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);
    $warehouse         = Warehouse::where('id',$warehouse_id)->first();
    $warehouse_rooms   = WarehouseRoom::where('warehouseid',$warehouse_id) -> get();

    return view('administration_warehouse_rooms')->with([
      'active_menu_item'          => 'administration',
      'active_sub_menu_item'      => 'warehouses',
      'title'                     => 'Administration',
      'user'                      => $user,
      'warehouse_rooms'           => $warehouse_rooms,
      'warehouse'                 => $warehouse,
      'language_resource'         => $language_resource
    ]);
  }

  public function postAddWarehouseRoom(Request $request)
  {
    $user              = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    $warehouse_room = WarehouseRoom::create([
      'warehouseid'        => $request->warehouseid,
      'name'               => $request->name,
      'isactive'           => $request->isactive,
    ]);

    if($request->image) {
      $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);

      $imageName = time().'.'.$request->image->extension();

      $request->image->move(public_path('images/rooms/'.$warehouse_room -> id), $imageName);

      $warehouse_room -> image = $imageName;
    }

    $warehouse_room -> save();

    // User Action
    $isactive = $warehouse_room->isactive ? $language_resource['aktiv'] : $language_resource['inactive'];
    $html = '';
    $html .= '<tr data-warehouse-room-id="'.$warehouse_room->id.'">';
    $html .= '<td class="td_id">'.$warehouse_room->id.'</td>';
    $html .= '<td class="td_wh_name">'.$warehouse_room->warehouse->name.'</td>';
    $html .= '<td>'.$warehouse_room->name.'</td>';
    $html .= '<td class="td_counts">'.$isactive.'</td>';
    $html .= '<td class="td_buttons"><a href="/administration/warehouse-room-sensors/'.$warehouse_room->id.'"><button class="btn btn-default"><i class="fas fa-map-marker-alt"></i></button></a></td>';
    $html .= '<td class="td_buttons">';
    $html .= '<button class="btn btn-primary" data-toggle="modal" data-target="#edit_warehouse_room_'.$warehouse_room->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
    $html .= '<div class="modal fade" id="edit_warehouse_room_'.$warehouse_room->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['edit'].'</h4></div>';
    $html .= '<div class="modal-body"><form id="edit_warehouse_room_form_'.$warehouse_room->id.'" method="POST" enctype="multipart/form-data">';
    $html .= '<input type="hidden" name="warehouse_id" value="'.$warehouse_room->warehouse->id.'">';
    $html .= '<input type="hidden" name="id" value="'.$warehouse_room->id.'">';
    $html .= '<div class="form-group"><label>'.$language_resource['desname'].'</label><input type="text" class="form-control" name="name" value="'.$warehouse_room->name.'"></div>';
    $html .= '<div class="form-group"><label>'.$language_resource['room_plan'].'</label><input type="file" name="image"></div>';
    $html .= '<div class="form-group"><label>'.$language_resource['status'].'</label><select class="form-control" name="isactive">';
    $html .= $warehouse_room->isactive ? '<option value="1">'.$language_resource['aktiv'].'</option><option value="0">'.$language_resource['inactive'].'</option>' : '<option value="0">'.$language_resource['inactive'].'</option><option value="1">'.$language_resource['aktiv'].'</option>';
    $html .= '</select></div>';
    $html .= '<div class="form-group"><button class="btn btn-primary submit_edit_warehouse_room_button" data-warehouse-room="'.$warehouse_room->id.'">'.$language_resource['edit'].'</button></div>';
    $html .= '</form></div></div></div></div></td>';
    $html .= '<td class="td_buttons">';
    $html .= '<button class="btn btn-danger" data-toggle="modal" data-target="#delete_room_'.$warehouse_room->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
    $html .= '<div class="modal fade" id="delete_room_'.$warehouse_room->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
    $html .= '<div class="modal-body"><p>'.$language_resource['deleteconf'].'?</p>';
    $html .= '<button class="btn btn-danger confirm-delete-warehouse" data-warehouse-room-id="'.$warehouse_room->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
    $html .= '</div></div></div></div></td></tr>';


    return response()->json([
      'success'             => 'ok',
      'warehouse_room'      => $warehouse_room,
      'html'                => $html,
      'message'             => 'ოთახი წარმატებით დაემატა',
      'alertType'           => 1
    ]);
  }

  public function postEditWarehouseRoom(Request $request)
  {
    $user              = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    $warehouse_room = WarehouseRoom::where('id', $request->input('id'))->first();

    if($request -> image)
    {
      $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
      ]);

      $imageName = time().'.'.$request -> image -> extension();

      $file = new Filesystem;
      $file->cleanDirectory('images/rooms/'.$warehouse_room -> id);

      $request -> image->move(public_path('images/rooms/'.$warehouse_room -> id), $imageName);

      $warehouse_room->update([
        'name' => $request -> input('name'),
        'isactive'    => $request -> input('isactive'),
        'image'     => $imageName
      ]);

    }else{
      $warehouse_room->update($request->except('image'));
    }


    // User Action

    $isactive = $warehouse_room->isactive ? $language_resource['aktiv'] : $language_resource['inactive'];
    $html = '';
    $html .= '<td class="td_id">'.$warehouse_room->id.'</td>';
    $html .= '<td class="td_wh_name">'.$warehouse_room->warehouse->name.'</td>';
    $html .= '<td>'.$warehouse_room->name.'</td>';
    $html .= '<td class="td_counts">'.$isactive.'</td>';
    $html .= '<td class="td_buttons"><a href="/administration/warehouse-room-sensors/'.$warehouse_room->id.'"><button class="btn btn-default"><i class="fas fa-map-marker-alt"></i></button></a></td>';
    $html .= '<td class="td_buttons">';
    $html .= '<button class="btn btn-primary" data-toggle="modal" data-target="#edit_warehouse_room_'.$warehouse_room->id.'"><span class="glyphicon glyphicon-pencil"></span></button>';
    $html .= '<div class="modal fade" id="edit_warehouse_room_'.$warehouse_room->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['edit'].'</h4></div>';
    $html .= '<div class="modal-body"><form id="edit_warehouse_room_form_'.$warehouse_room->id.'" method="POST" enctype="multipart/form-data">';
    $html .= '<input type="hidden" name="warehouse_id" value="'.$warehouse_room->warehouse->id.'">';
    $html .= '<input type="hidden" name="id" value="'.$warehouse_room->id.'">';
    $html .= '<div class="form-group"><label>'.$language_resource['desname'].'</label><input type="text" class="form-control" name="name" value="'.$warehouse_room->name.'"></div>';
    $html .= '<div class="form-group"><label>'.$language_resource['room_plan'].'</label><input type="file" name="image"></div>';
    $html .= '<div class="form-group"><label>'.$language_resource['status'].'</label><select class="form-control" name="isactive">';
    $html .= $warehouse_room->isactive ? '<option value="1">'.$language_resource['aktiv'].'</option><option value="0">'.$language_resource['inactive'].'</option>' : '<option value="0">'.$language_resource['inactive'].'</option><option value="1">'.$language_resource['aktiv'].'</option>';
    $html .= '</select></div>';
    $html .= '<div class="form-group"><button class="btn btn-primary submit_edit_warehouse_room_button" data-warehouse-room="'.$warehouse_room->id.'">'.$language_resource['edit'].'</button></div>';
    $html .= '</form></div></div></div></div></td>';
    $html .= '<td class="td_buttons">';
    $html .= '<button class="btn btn-danger" data-toggle="modal" data-target="#delete_room_'.$warehouse_room->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
    $html .= '<div class="modal fade" id="delete_room_'.$warehouse_room->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
    $html .= '<div class="modal-body"><p>'.$language_resource['deleteconf'].'?</p>';
    $html .= '<button class="btn btn-danger confirm-delete-warehouse" data-warehouse-room-id="'.$warehouse_room->id.'"><span class="glyphicon glyphicon-trash"></span></button>';
    $html .= '</div></div></div></div></td>';


    return response()->json([
      'success'             => 'ok',
      'warehouse_room'      => $warehouse_room,
      'html'                => $html,
      'message'             => 'ოთახი წარმატებით შეიცვალა',
      'alertType'           => 1
    ]);
  }

  public function getDeleteWarehouseRoom($roomid)
  {
    $warehouse_room = WarehouseRoom::where('id',$roomid)->delete();

    // User Action

    return response()->json([
      'success'             => 'ok',
      'message'             => 'ოთახი წარმატებით წაიშალა',
      'alertType'           => 1
    ]);
  }

  public function getAddWarehouseRoomSensors($roomid)
  {
    $user    = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    $controllers = SensorsController::select('tempohub_controllers.id', 'd.name as controller_name', 'tempohub_controllers.imei', 'tempohub_controllers.isactive', 'w.name')->join('device as d', 'd.imei', '=', 'tempohub_controllers.imei')->join('tempohub_warehouses as w', 'w.id', '=', 'tempohub_controllers.warehouseid')->join('tempohub_rooms as r', 'r.warehouseid', '=', 'w.id')->where('r.id', $roomid)->get();

    $sensors = Sensor::select('tempohub_sensors.id', 'tempohub_sensors.name','tempohub_sensors.index', 'tempohub_sensors.isactive','d.name as controller_name')->join('tempohub_roomsensors as tr', 'tr.sensorid', 'tempohub_sensors.id')->join('device as d', 'd.imei', '=', 'tempohub_sensors.imei')->where('tr.roomid',$roomid) -> get();

    $room_pins = WarehouseRoomSensor::select('tempohub_roomsensors.id as roomsensorid', 'tempohub_roomsensors.roomid', 'tempohub_roomsensors.map_x', 'tempohub_roomsensors.map_y', 's.id', 's.imei as sensorimei', 's.index', 's.isactive', 's.name', 'd.name as controller_name', 'tempohub_roomsensors.sensorid')->leftjoin('tempohub_sensors as s', 's.id', '=', 'tempohub_roomsensors.sensorid')->leftjoin('device as d', 'd.imei', '=', 's.imei')->where('tempohub_roomsensors.roomid', $roomid)->orderByRaw('s.isactive desc NULLS LAST')->get();

    $room         = WarehouseRoom::where('id',$roomid) -> first();

    return view('administration_warehouse_room_sensors')->with([
      'active_menu_item'          => 'administration',
      'active_sub_menu_item'      => 'warehouses',
      'title'                     => 'Administration',
      'user'                      => $user,
      'controllers'               => $controllers,
      'sensors'                   => $sensors,
      'room'                      => $room,
      'roompins'                  => $room_pins,
      'language_resource'         => $language_resource
    ]);
  }

  public function getControllerSensors(Request $request) {
    $sensors = Sensor::select('tempohub_sensors.id', 'tempohub_sensors.name', 'tempohub_sensors.index', 'tempohub_sensors.imei', 'tempohub_sensors.isactive')->join('tempohub_controllers as c', 'c.imei', '=', 'tempohub_sensors.imei')->leftjoin('tempohub_roomsensors as tr', 'tr.sensorid', '=', 'tempohub_sensors.id')->where('c.id', $request->controllerid)->whereNull('tr.sensorid')->get();

    return response()->json([
      'success'     => 'ok',
      'sensors'     => $sensors
    ]);
  }

  public function getRoomPoints(Request $request) {

    $roompoints = WarehouseRoomSensor::select('tempohub_roomsensors.id', 'tempohub_roomsensors.roomid', 'map_x', 'map_y', 's.id as sensorid', 's.index', 's.isactive')->leftjoin('tempohub_sensors as s', 's.id', '=', 'tempohub_roomsensors.sensorid')->where('tempohub_roomsensors.roomid', $request->roomid)->get();

    return response()->json([
      'success'     => 'ok',
      'points'      => $roompoints
    ]);
  }

  public function getDeviceSensorsOrders(Request $request)
  {

    $warehouse_room_sensors = WarehouseRoomSensor::where('imei',$request -> imei)->orderBy('temperaturedata_back_index','DESC')->get();

    $order_indexes = [0,1,2,3,4,5,6,7];

    foreach($warehouse_room_sensors as $warehouse_room_sensor)
    {
      unset($order_indexes[$warehouse_room_sensor -> temperaturedata_back_index]);
    }

    return response()->json([
      'success'       =>'ok',
      'order_indexes' => $order_indexes
    ]);

  }

  public function postAddWarehouseRoomSensor(Request $request)
  {
    $user    = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    if(is_null($request->input('map_x')) || is_null($request->input('map_y'))) {
      return redirect()->back()->with('errormessage', 'აუცილებელია რუკაზე მიუთითოთ წერტილი');
    }

    $warehouse_room_sensor = WarehouseRoomSensor::create([
      'roomid'        => $request->input('roomid'),
      'map_x'         => $request->input('map_x'),
      'map_y'         => $request->input('map_y'),
      'sensorid'      => $request->input('sensorid'),
    ]);

    $sensor = Sensor::select('tempohub_sensors.id', 'tempohub_sensors.name','tempohub_sensors.index', 'tempohub_sensors.isactive','d.name as controller_name', 'tr.id as roomsensorid')->join('tempohub_roomsensors as tr', 'tr.sensorid', 'tempohub_sensors.id')->join('device as d', 'd.imei', '=', 'tempohub_sensors.imei')->where('tempohub_sensors.id', $request->input('sensorid'))->first();

    if(!is_null($request->input('sensornewname'))) {
      $sensor -> update([
        'name'      => $request -> input('sensornewname')
      ]);
    }

    $controllers = SensorsController::select('tempohub_controllers.id', 'd.name as controller_name', 'tempohub_controllers.imei', 'tempohub_controllers.isactive', 'w.name')->join('device as d', 'd.imei', '=', 'tempohub_controllers.imei')->join('tempohub_warehouses as w', 'w.id', '=', 'tempohub_controllers.warehouseid')->join('tempohub_rooms as r', 'r.warehouseid', '=', 'w.id')->where('r.id', $warehouse_room_sensor->roomid)->get();

    $room = WarehouseRoom::where('id',$warehouse_room_sensor->roomid) -> first();
    $isactive = $sensor->isactive ? $language_resource['aktiv'] : $language_resource['inactive'];
    $html = '';
    if(isset($sensor -> id)) {
      $html .= '<tr class="point-row" data-pin-checked="'.$sensor->roomsensorid.'">';
      $html .= '<td>'.$sensor->controller_name.'</td>';
      $html .= '<td>'.$sensor->name.'</td>';
      $html .= '<td>'.$sensor->index.'</td>';
      $html .= '<td class="td_counts">'.$isactive.'</td>';
      $html .= '<td class="td_buttons"><a href="/administration/alerts"><button class="btn btn-sm btn-default"><i class="fas fa-satellite-dish"></i></button></a></td>';
    } else {
      $html .= '<tr class="point-row" data-pin-checked="'.$warehouse_room_sensor->id.'"><td colspan="5">'.$language_resource['sensornotatached'].'</td>';
    }

    $html .= '<td class="td_buttons"><div class="btn-group">';
    if(isset($sensor->id)) {
      $html .= '<button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#unlink_sensor_'.$sensor->id.'" title="'.$language_resource['detach'].'"><i class="fas fa-unlink"></i></button>';
    } else {
      $html .= '<button class="btn btn-sm btn-warning link-sensor-btn" data-toggle="modal" data-sensor-roomsensorid="'.$warehouse_room_sensor->id.'" data-room-id="{{ $sensor -> roomid }}" data-target="#link_sensor_'.$warehouse_room_sensor->id.'" title="'.$language_resource['attach'].'"><i class="fas fa-link"></i></button>';
    }
    $html .= '<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#delete_point_'.$warehouse_room_sensor->id.'" title="'.$language_resource['delete'].'"><span class="glyphicon glyphicon-trash"></span></button></div>';
    $html .= '<div class="modal fade" id="link_sensor_'.$warehouse_room_sensor->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['add'].'</h4></div>';
    $html .= '<div class="modal-body">';
    $html .= '<form id="link-sensor-2-point-form_'.$warehouse_room_sensor->id.'" method="POST">';
    $html .= '<div class="form-group"><label for="controller">'.$language_resource['controller'].'</label><select class="form-control add_controller" name="controller" id="add_controller"><option value=""></option>';
    foreach($controllers as $controller) {
      $html .= '<option value="'.$controller->id.'">'.$controller->controller_name.'</option>';
    }
    $html .= '</select></div>';
    $html .= '<div class="form-group"><div class="row">';
    $html .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label for="sensors">'.$language_resource['sensor'].'</label><select class="form-control add_sensors" name="sensorid" id="add_sensors" disabled></select></div>';
    $html .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label for="sensornewname">'.$language_resource['new'].$language_resource['desname'].'</label><input type="text" name="sensornewname" class="form-control" /></div></div></div>';
    $html .= '<div class="form-group"><div class="room_plan_drawer_main_container"><div class="room_plan_drawer_container_edit" id="room_plan_drawer_container_link_'.$warehouse_room_sensor->id.'"><img src="/images/rooms/'.$room->id.'/'.$room->image.'" alt=""></div></div></div>';
    $html .= '<div class="form-group"><div class="row"><div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label>'.$language_resource['map'].'_X</label><input type="text" name="map_x" class="form-control" readonly id="map_x"></div>';
    $html .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label>'.$language_resource['map'].'_Y</label><input type="text" name="map_y" class="form-control" readonly id="map_y"></div></div></div> ';
    $html .= '<div class="form-group"><button class="btn btn-primary link-sensor-2-point-btn" data-point-id="'.$warehouse_room_sensor->id.'">'.$language_resource['add'].'</button></div></form></div></div></div></div>';
    $html .= '<div class="modal fade" id="delete_point_'.$warehouse_room_sensor->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
    $html .= '<div class="modal-body"><p>'.$language_resource['deleteconf'].'</p><button class="btn btn-danger confirm-delete-point" data-point-id="'.$warehouse_room_sensor->id.'""><span class="glyphicon glyphicon-trash"></span></button></div></div></div></div>';
    if(isset($sensor->id)) {
      $html .= '<div class="modal fade" id="unlink_sensor_'.$sensor->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
      $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
      $html .= '<div class="modal-body"><p>'.$language_resource['warningdetach'].'</p><button class="btn btn-warning confirm-unlink-sensor" data-point-id="'.$warehouse_room_sensor->sensorid.'"><i class="fas fa-unlink"></i></button></div></div></div></div>';
    }
    $html .= '</td></tr>';

    // User Action

    return response()->json([
      'success'  =>'ok',
      'point'    => $warehouse_room_sensor,
      'sensor'   => $sensor,
      'html'     => $html,
      'message'              => 'წერტილი წარმატებით დაემატა',
      'alertType'            => 1
    ]);
  }

  public function postAddSensorToPoint(Request $request)
  {
    $user    = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    $roomsensor = WarehouseRoomSensor::where('id', $request->input('roomsensorid'))->first();

    if(!is_null($request->input('sensornewname'))) {
      $sensor_with_name = Sensor::where('name', $request->input('sensornewname'))->first();
      if(!is_null($sensor_with_name)) {
        return redirect()->back()->with('errormessage', 'სენსორი ასეთი სახელით უკვე არსებობს');
      }
      $sensor_to_link = Sensor::where('id', $request->input('sensorid'))->first();
      $sensor_to_link->update([
        'name'      => $request -> input('sensornewname')
      ]);

      if(!is_null($request->input('sensorid'))) {
        $roomsensor->update([
          'sensorid'  => $request -> input('sensorid')
        ]);
      }
    } else {
      if(!is_null($request->input('sensorid'))) {
        $roomsensor->update([
          'sensorid'  => $request -> input('sensorid')
        ]);
      }
    }

    $sensor = Sensor::select('tempohub_sensors.id', 'tempohub_sensors.name','tempohub_sensors.index', 'tempohub_sensors.isactive','d.name as controller_name', 'tr.id as roomsensorid')->join('tempohub_roomsensors as tr', 'tr.sensorid', 'tempohub_sensors.id')->join('device as d', 'd.imei', '=', 'tempohub_sensors.imei')->where('tempohub_sensors.id', $request->input('sensorid'))->first();

    $controllers = SensorsController::select('tempohub_controllers.id', 'd.name as controller_name', 'tempohub_controllers.imei', 'tempohub_controllers.isactive', 'w.name')->join('device as d', 'd.imei', '=', 'tempohub_controllers.imei')->join('tempohub_warehouses as w', 'w.id', '=', 'tempohub_controllers.warehouseid')->join('tempohub_rooms as r', 'r.warehouseid', '=', 'w.id')->where('r.id', $roomsensor->roomid)->get();
    $room = WarehouseRoom::where('id',$roomsensor->roomid) -> first();

    // User Action

    $isactive = $sensor->isactive ? $language_resource['aktiv'] : $language_resource['inactive'];
    $html = '';
    if($sensor -> id) {
      $html .= '<td>'.$sensor->controller_name.'</td>';
      $html .= '<td>'.$sensor->name.'</td>';
      $html .= '<td>'.$sensor->index.'</td>';
      $html .= '<td class="td_counts">'.$isactive.'</td>';
      $html .= '<td class="td_buttons"><a href="/administration/alerts"><button class="btn btn-sm btn-default"><i class="fas fa-satellite-dish"></i></button></a></td>';
    } else {
      $html .= '<td colspan="5">'.$language_resource['sensornotatached'].'</td>';
    }

    $html .= '<td class="td_buttons"><div class="btn-group">';
    if($sensor->id) {
      $html .= '<button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#unlink_sensor_'.$sensor->id.'" title="'.$language_resource['detach'].'"><i class="fas fa-unlink"></i></button>';
    } else {
      $html .= '<button class="btn btn-sm btn-warning link-sensor-btn" data-toggle="modal" data-sensor-roomsensorid="'.$sensor->roomsensorid.'" data-room-id="{{ $sensor -> roomid }}" data-target="#link_sensor_'.$sensor->roomsensorid.'" title="'.$language_resource['attach'].'"><i class="fas fa-link"></i></button>';
    }
    $html .= '<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#delete_point_'.$sensor->roomsensorid.'" title="'.$language_resource['delete'].'"><span class="glyphicon glyphicon-trash"></span></button></div>';
    $html .= '<div class="modal fade" id="link_sensor_'.$sensor->roomsensorid.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['add'].'</h4></div>';
    $html .= '<div class="modal-body">';
    $html .= '<form id="link-sensor-2-point-form_'.$sensor->roomsensorid.'" method="POST"><input type="hidden" name="roomsensorid" value="'.$sensor->roomsensorid.'">';
    $html .= '<div class="form-group"><label for="controller">'.$language_resource['controller'].'</label><select class="form-control add_controller" name="controller" id="add_controller"><option value=""></option>';
    foreach($controllers as $controller) {
      $html .= '<option value="'.$controller->id.'">'.$controller->controller_name.'</option>';
    }
    $html .= '</select></div>';
    $html .= '<div class="form-group"><div class="row">';
    $html .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label for="sensors">'.$language_resource['sensor'].'</label><select class="form-control add_sensors" name="sensorid" id="add_sensors" disabled></select></div>';
    $html .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label for="sensornewname">'.$language_resource['new'].$language_resource['desname'].'</label><input type="text" name="sensornewname" class="form-control" /></div></div></div>';
    $html .= '<div class="form-group"><div class="room_plan_drawer_main_container"><div class="room_plan_drawer_container_edit" id="room_plan_drawer_container_link_'.$sensor->roomsensorid.'"><img src="/images/rooms/'.$room->id.'/'.$room->image.'" alt=""></div></div></div>';
    $html .= '<div class="form-group"><div class="row"><div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label>'.$language_resource['map'].'_X</label><input type="text" name="map_x" class="form-control" readonly id="map_x"></div>';
    $html .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label>'.$language_resource['map'].'_Y</label><input type="text" name="map_y" class="form-control" readonly id="map_y"></div></div></div> ';
    $html .= '<div class="form-group"><button class="btn btn-primary link-sensor-2-point-btn" data-point-id="'.$sensor->roomsensorid.'>'.$language_resource['add'].'</button></div></form></div></div></div></div>';
    $html .= '<div class="modal fade" id="delete_point_'.$sensor->roomsensorid.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
    $html .= '<div class="modal-body"><p>'.$language_resource['deleteconf'].'</p><button class="btn btn-danger confirm-delete-point" data-point-id="'.$sensor->roomsensorid.'"><span class="glyphicon glyphicon-trash"></span></button></div></div></div></div>';
    $html .= '<div class="modal fade" id="unlink_sensor_'.$sensor->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
    $html .= '<div class="modal-body"><p>'.$language_resource['warningdetach'].'</p><button class="btn btn-warning confirm-unlink-sensor" data-point-id="'.$sensor->id.'""><i class="fas fa-unlink"></i></button></div></div></div></div></td></tr>';


    return response()->json([
      'success'    =>'ok',
      'roomsensor' => $roomsensor,
      'sensor'    => $sensor,
      'html'       => $html,
      'message'              => 'სენსორი წარმატებით მიება წერტილს',
      'alertType'            => 1
    ]);
  }

  public function getDeleteWarehouseRoomSensor($sensorid)
  {
    $roomsensors = WarehouseRoomSensor::where('id', $sensorid)->delete();

    // User Action

    return response()->json([
      'success'    =>'ok',
      'message'              => 'წერტილი წარმატებით წაიშალა',
      'alertType'            => 1
    ]);
  }

  public function getUnlinkWarehouseRoomSensor($sensorid)
  {
    $user    = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    $roomsensor = WarehouseRoomSensor::where('sensorid', $sensorid)->first();
    $roomsensor->update(['sensorid' => null]);

    $controllers = SensorsController::select('tempohub_controllers.id', 'd.name as controller_name', 'tempohub_controllers.imei', 'tempohub_controllers.isactive', 'w.name')->join('device as d', 'd.imei', '=', 'tempohub_controllers.imei')->join('tempohub_warehouses as w', 'w.id', '=', 'tempohub_controllers.warehouseid')->join('tempohub_rooms as r', 'r.warehouseid', '=', 'w.id')->where('r.id', $roomsensor->roomid)->get();

    $room = WarehouseRoom::where('id',$roomsensor->roomid) -> first();
    // User Action

    $html = '';
    $html .= '<td colspan="5">'.$language_resource['sensornotatached'].'</td>';
    $html .= '<td class="td_buttons"><button class="btn btn-sm btn-warning link-sensor-btn" data-toggle="modal" data-sensor-roomsensorid="'.$roomsensor->id.'" data-room-id="'.$roomsensor->roomid.'" data-target="#link_sensor_'.$roomsensor->id.'" title="'.$language_resource['attach'].'"><i class="fas fa-link"></i></button>';
    $html .= '<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#delete_point_'.$roomsensor->id.'" title="'.$language_resource['delete'].'"><span class="glyphicon glyphicon-trash"></span></button></div>';
    $html .= '<div class="modal fade" id="link_sensor_'.$roomsensor->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['add'].'</h4></div>';
    $html .= '<div class="modal-body"><form id="link-sensor-2-point-form_'.$roomsensor->id.'" method="POST">';
    $html .= '<input type="hidden" name="roomsensorid" value="'.$roomsensor->id.'">';
    $html .= '<div class="form-group"><label for="controller">'.$language_resource['controller'].'</label><select class="form-control add_controller" name="controller" id="add_controller"><option value=""></option>';
    foreach($controllers as $controller) {
      $html .= '<option value="'.$controller->id.'">'.$controller->controller_name.'</option>';
    }
    $html .= '</select></div>';
    $html .= '<div class="form-group"><div class="row"><div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label for="sensors">'.$language_resource['sensor'].'</label><select class="form-control add_sensors" name="sensorid" id="add_sensors" disabled></select></div><div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label for="sensornewname">'.$language_resource['new'].$language_resource['desname'].'</label><input type="text" name="sensornewname" class="form-control" /></div></div></div>';
    $html .= '<div class="form-group">';
    $html .= '<div class="room_plan_drawer_main_container"><div class="room_plan_drawer_container_edit" id="room_plan_drawer_container_link_'.$roomsensor->id.'"><img src="/images/rooms/'.$room->id.'/'.$room->image.'" alt=""></div></div></div>';
    $html .= '<div class="form-group"><div class="row"><div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label>'.$language_resource['map'].'_X</label><input type="text" name="map_x" class="form-control" readonly id="map_x"></div><div class="col-lg-6 col-md-6 col-sm-6 col-xs-6"><label>'.$language_resource['map'].'_Y</label><input type="text" name="map_y" class="form-control" readonly id="map_y"></div></div></div> ';
    $html .= '<div class="form-group"><button class="btn btn-primary link-sensor-2-point-btn" data-point-id="'.$roomsensor->id.'">'.$language_resource['add'].'</button></div></form></div></div></div></div>';
    $html .= '<div class="modal fade" id="delete_point_'.$roomsensor->id.'" tabindex="-1" role="dialog" aria-labelledby="myModalLabel"><div class="modal-dialog" role="document"><div class="modal-content">';
    $html .= '<div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button><h4 class="modal-title" id="myModalLabel">'.$language_resource['delete'].'</h4></div>';
    $html .= '<div class="modal-body"><p>'.$language_resource['deleteconf'].'</p><button class="btn btn-danger confirm-delete-point" data-point-id="'.$roomsensor->id.'"><span class="glyphicon glyphicon-trash"></span></button></div></div></div></div></td>';

    return response()->json([
      'success'      =>'ok',
      'html'         => $html,
      'roomsensorid' => $roomsensor->id,

      'message'              => 'სენსორი წარმატებით მოიხსნა წერტილიდან',
      'alertType'            => 1
    ]);
  }

  public function postEditWarehouseRoomSensor(Request $request)
  {

    $warehouse_room_sensor = WarehouseRoomSensor::where('id',$request -> input('id'))->first();

    $warehouse_room_sensor -> update($request->all());

    return redirect() -> back();
  }

  public function getAdministrationAlerts()
  {
    $user      = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);
    $alerts    = Alert::where('firmaid',$user -> firmaid)-> orderBy('id','DESC') -> get();
    $devices   = Device::where([['firmaid', $user -> firmaid],['enable',true]]) -> get();
    $employers = User::where('firmaid',$user -> firmaid) -> get();
    $warehouses = Warehouse::where('firmaid', $user->firmaid)->get();

    return view('administration_alerts')->with([
      'active_menu_item'     => 'administration',
      'active_sub_menu_item' => 'alerts',
      'title'                => 'Administration',
      'user'                 => $user,
      'warehouses'           => $warehouses,
      'devices'              => $devices,
      'alerts'               => $alerts,
      'employers'            => $employers,
      'language_resource'    => $language_resource
    ]);
  }

  public function getControllersByWarehouse(Request $request) {
    // $controllers = SensorsController::select('tempohub_controllers.id as controllerid', 'tempohub_controllers.imei as imei', 'tempohub_controllers.isactive as isactive', 'd.name as controllername')->leftjoin('device as d', 'd.imei', '=', 'tempohub_controllers.imei')->where('warehouseid', $request->warehouseid)->get();

    return response()->json([
      'success'       => 'ok',
      'controllers'   => 'kontrolerebi'
    ]);
  }

  public function getSensorsByControllers(Request $request) {
    $sensors = Sensor::where('imei', $request->imei)->get();

    return response()->json([
      'success'       => 'ok',
      'sensors'   => $sensors
    ]);
  }

  public function getAdministrationSensors()
  {
    $user      = Auth::user();
    $language_resource = Resource::getResourceForLanguage($user->language);

    $sensors = Sensor::select('tempohub_sensors.id','tempohub_sensors.name as sensor_name','tempohub_sensors.isactive','d.name as controller_name','w.name as warehouse_name','tr.sensorid','r.name as room_name')->leftjoin('tempohub_controllers as c','c.imei','=','tempohub_sensors.imei')->leftjoin('device as d','d.imei','=','tempohub_sensors.imei')->leftjoin('tempohub_warehouses as w','w.id','=','c.warehouseid')->leftjoin('users as u','u.firmaid','=','w.firmaid')->leftjoin('tempohub_roomsensors as tr', 'tr.sensorid','=','tempohub_sensors.id')->leftjoin('tempohub_rooms as r','r.id','=','tr.roomid')->where('u.nickname',$user->nickname)->orderByRaw('tempohub_sensors.isactive desc')->get();

    return view('administration_sensors')->with([
      'active_menu_item'     => 'administration',
      'active_sub_menu_item' => 'sensors',
      'title'                => 'Administration',
      'user'                 => $user,
      'language_resource'    => $language_resource,
      'sensors'              => $sensors
    ]);
  }

  public function getRooms(Request $request)
  {
    $rooms = WarehouseRoom::where('warehouseid', $request->warehouseid)->get();

    return response()->json([
      'success'       => 'ok',
      'rooms'          => $rooms
    ]);
  }

  public function getSensors(Request $request)
  {
    $sensors = Sensor::select('tempohub_sensors.id', 'tempohub_sensors.imei', 'tempohub_sensors.index', 'tempohub_sensors.name')->leftjoin('tempohub_roomsensors as tr', 'tr.sensorid', '=', 'tempohub_sensors.id')->where('tr.roomid', $request->roomid)->get();

    return response()->json([
      'success'      => 'ok',
      'sensors'      => $sensors
    ]);
  }

}

