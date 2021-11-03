@extends('master')

@section('body')
    @include('header')
    <div class="container row monitoring-div">
        <div class="col list-div">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 monitoring-warehouse-select-div">
                <select class="multiselect" multiple="multiple" name="warehouse_rooms_selector[]" id="warehouses_multiselect">
                    @foreach($warehouses as $warehouse)
                        <option value="{{ $warehouse -> id }}">{{ $warehouse -> name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 monitoring-objects-list-div">
                <div class="panel-group" id="monitoring_room_list_accordion">
                    @foreach($warehouses as $warehouse)
                    <div class="panel panel-default warehouse-div warehouse-div-on" id="warehouse_{{ $warehouse -> id }}">
                        <a class="accordion-link accordion-warehouse-link" data-toggle="collapse" data-parent="#monitoring_room_list_accordion" href="#collapse_warehouse_{{ $warehouse -> id }}">
                            <div class="panel-heading warehouse-panel-heading opened-warehouse">
                                <div class="row">
                                    <div class="col-lg-10 col-xs-10"><h4 class="panel-title">{{ $warehouse -> name }}</h4></div>
                                    <div class="col-lg-2 col-xs-2"><i class="fas fa-chevron-right arrow-icon"></i></div>
                                </div>
                            </div>
                        </a>
                        <div id="collapse_warehouse_{{ $warehouse -> id}}" class="panel-collapse collapse">
                            <div class="panel-group rooms-panel" id="monitoring_sensor_list_accordion">
                                @foreach ($warehouse -> warehouse_rooms as $warehouse_room)
                                <div class="panel panel-default">
                                    <a class="accordion-link accordion-room-link" data-toggle="collapse" data-parent="#monitoring_sensor_list_accordion" href="#collapse_room_{{ $warehouse_room -> id }}">
                                        <div class="panel-heading room-panel-heading" id="room_{{ $warehouse_room -> id }}" data-room-id="{{ $warehouse_room -> id }}">
                                             <div class="row">
                                                 <table class="table">
                                                     <tr>
                                                        <td class="sensor-name-td"><p class="sensor-name-box">{{ $warehouse_room -> name }}</p></td>
                                                        <td style="width: 20%" class="average-temperature"><i class="fas fa-thermometer-half"></i> {{ $warehouse_room['avgTempo'] }}°C </td>
                                                        <td style="width: 20%" class="average-humidity"><i class="fas fa-tint"></i> {{ $warehouse_room['avgHumi'] }}% </td>
                                                     </tr>
                                                 </table>
                                            </div>
                                        </div>
                                    </a>
                                    <div id="collapse_room_{{ $warehouse_room -> id }}" class="panel-collapse collapse sensors-div">
                                        @foreach($warehouse_room -> sensors -> sortBy('index') as $sensor)
                                            @if($sensor->isactive && $sensor->transmission)
                                                @php
                                                    $currentdata = $sensor -> transmission -> temperature_data_backs -> where('index', $sensor -> index)->first();
                                                    $noSignalColor = '#888888';
                                                    $temperaturaIconColor = ($currentdata->tempo <= $sensor->mintemp || $currentdata->tempo >= $sensor->maxtemp) ? '#EF5F5F' : '#15BB66 ';
                                                    $humidityIconBg = ($currentdata->humidity <= $sensor->minhum || $currentdata->humidity >= $sensor->maxhum) ? '#EF5F5F' : '#15BB66 ';
                                                    $batteryIconColor = ($currentdata->battery_proc < 5) ? '#EF5F5F' : '#15BB66 ';
                                                @endphp
                                                <div class="sensor-div get-sensor-graphycs" id="sensor_{{ $sensor -> id }}" data-sensor-id="{{ $sensor -> id }}">
                                                    <div class="arrow-left"></div>
                                                    <div class="container tooltip-for-sensor">
                                                        <table>
                                                            <tr>
                                                                <td colspan="3">{{ $sensor -> name }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3"><i class="fas fa-clock"></i> {{ $sensor -> transmission -> datestamploc }}</td>
                                                            </tr>
                                                            <tr>
                                                            @if($currentdata->tempo)
                                                                <td class="temperature-td"><i class="fas fa-thermometer-half" style="color: {{ $temperaturaIconColor }}"></i> {{ round($currentdata->tempo, 1) }}°C</td>
                                                            @else
                                                                <td class="temperature-td"></td>
                                                            @endif
                                                            @if($currentdata->humidity)
                                                                <td class="humidity-td"><i class="fas fa-tint" style="color: {{ $humidityIconBg }}"></i> {{ round($currentdata->humidity, 1) }}%</td>
                                                            @else
                                                                <td class="humidity-td"></td>
                                                            @endif
                                                            @if($currentdata->battery_proc)
                                                                <td class="battery-td"><i class="fas fa-battery-half" style="color: {{ $batteryIconColor }}"></i> {{ $currentdata->battery_proc }}%</td>
                                                            @else
                                                                <td class="battery-td"></td>
                                                            @endif
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <table class="table">
                                                        <tr>
                                                            <td class="sensor-name-td"><p class="sensor-name-box">{{ $sensor -> name }}</p></td>
                                                            @if($currentdata->tempo)
                                                                <td style="width: 20%" class="temperature-td"><i class="fas fa-thermometer-half" style="color: {{ $temperaturaIconColor }}"></i> {{ round($currentdata->tempo, 1) }}°C</td>
                                                            @else
                                                                <td class="temperature-td" style="width: 15%"></td>
                                                            @endif
                                                            @if($currentdata->humidity)
                                                                <td style="width: 15%" class="humidity-td"><i class="fas fa-tint" style="color: {{ $humidityIconBg }}"></i> {{ round($currentdata->humidity, 1) }}%</td>
                                                            @else
                                                                <td class="humidity-td" style="width: 15%"></td>
                                                            @endif
                                                            @if($currentdata->battery_proc)
                                                                @if($currentdata->battery_proc < 5)
                                                                    <td style="width: 15%" class="battery-td"><i class="fas fa-battery-half" style="color: {{ $batteryIconColor }}"></i> {{ $currentdata->battery_proc }}%</td>
                                                                @endif
                                                            @endif
                                                        </tr>
                                                    </table>
                                                </div>
                                            @else
                                                <div class="sensor-div get-sensor-graphycs" id="sensor_{{ $sensor -> id }}" data-sensor-id="{{ $sensor -> id }}">
                                                    <div class="arrow-left"></div>
                                                    <div class="container tooltip-for-sensor">
                                                        <table>
                                                            <tr>
                                                                <td colspan="3">{{ $sensor -> name }}</td>
                                                            </tr>
                                                            <tr>
                                                                @if($sensor -> transmission)
                                                                    <td colspan="3"><i class="fas fa-clock"></i> {{ $sensor -> transmission -> datestamploc }}</td>
                                                                @endif
                                                            </tr>
                                                            <tr>
                                                                <td colspan="3">სენსორი არ არის აქტიური</td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                    <table class="table">
                                                        <tr class="d-flex">
                                                            <td class="sensor-name-td"><p class="sensor-name-box">{{ $sensor -> name }}</p></td>
                                                            <td style="width: 20%" class="temperature-td"><i class="fas fa-thermometer-half" style="color: #888888"></i></td>
                                                            <td style="width: 15%" class="humidity-td"><i class="fas fa-tint" style="color: #888888"></i></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col graphycs-div">
            <div class="panel with-nav-tabs panel-default">
                <div class="panel-heading">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#graphycsTab" data-toggle="tab">
                                <i class="fas fa-chart-area"></i>
                            </a>
                        </li>
                        <li id="mapDivTab">
                            <a href="#mapTab" data-toggle="tab">
                                <i class="fas fa-map-marked-alt"></i>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane fade in active" id="graphycsTab">
                            <figure class="highcharts-figure">
                                <div id="monitoring_graphycs_div" style="width: 100%;"></div>
                            </figure>
                        </div>
                        <div class="tab-pane fade" id="mapTab">
                            <div class="container-fluid room-map-div"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('footer')
@endsection

@section('css')

@endsection

@section('js')

    <script type="text/javascript" src="{{ url('/js/monitoring.js') }}"></script>
    <script type="text/javascript" src="{{ url('/js/bootstrap-multiselect.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            if($('#monitoring_graphycs_div').html() == ''){

                //$('.accordion-warehouse-link').collapse('toggle');

                $('#sensor_1').trigger('click');
            }
        });


    </script>
@endsection
