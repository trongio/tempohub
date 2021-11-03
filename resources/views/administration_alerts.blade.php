@extends('master')

@section('body')
    @include('header')
    <div class="container-fluid reports_container_fluid">
        <div class="col-md-12 reports_main_container">
            <div class="col-md-12 reports_header">
                @include('administration_sub_menu')
            </div>
            <div class="col-md-12 col-sm-12 alert alert-success" role="alert"></div>
            <div class="col-md-12 alerts_header_container">
                <div class="left">
                    {{ $language_resource['alerts'] }}
                </div>
                <div class="right">
                    <button class="btn btn-primary" data-toggle="modal" data-target="#add_alert"><i class="far fa-plus-square"></i> {{ $language_resource['add'] }}</button>
                    <div class="modal fade" id="add_alert" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                    <h4 class="modal-title" id="myModalLabel">{{ $language_resource['add'] }}</h4>
                                </div>
                                <div class="modal-body">
                                    <form id="add_warehouse_room_sensor_alert_form" method="POST" class="alerts_target">
                                        @csrf
                                        <div id="smartwizardadd" class="smartwizard">
                                            <ul>
                                                <li><a href="#step-1"><small>{{ $language_resource['configuration'] }}</small></a></li>
                                                <li><a href="#step-2"><small>{{ $language_resource['devices'] }}</small></a></li>
                                                <li><a href="#step-3"><small>{{ $language_resource['notifications'] }}</small></a></li>
                                                <li><a href="#step-4"><small>{{ $language_resource['schedule'] }}</small></a></li>
                                                <li><a href="#step-5"><small>{{ $language_resource['users'] }}</small></a></li>
                                            </ul>
                                            <div>
                                                <!-- კონფიგურაცია -->
                                                <div id="step-1">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ $language_resource['desname'] }}</label>
                                                                <input type="text" class="form-control" name="alert_name" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ $language_resource['alarm'] }}</label>
                                                                <select class="form-control" name="alert_type">
                                                                    <option value="1">{{ $language_resource['on-temp-rise'] }}</option>
                                                                    <option value="0">{{ $language_resource['on-temp-decrease'] }}</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-3">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ $language_resource['bound'] }} (°C):</label>
                                                                <input type="number" class="form-control" name="alert_limit" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ $language_resource['exceeding-allowed-time'] }} ({{ $language_resource['minute'] }}):</label>
                                                                <input type="number" class="form-control" name="alert_time" min="1" step="1" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- მოწყობილობის არჩევა -->
                                                <div id="step-2">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>{{ $language_resource['warehouse'] }}</label>
                                                                <select class="form-control alerts_device" name="alert_device" id="warehouse_list">
                                                                    <option value="0">{{ $language_resource['warehouse_select'] }}</option>
                                                                    @foreach($warehouses as $warehouse)
                                                                        <option value="{{ $warehouse -> id }}">{{ $warehouse -> name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ $language_resource['device'] }}</label>
                                                                <select class="form-control alerts_device" name="alert_device" id="device_list" disabled>
                                                                    <option value="0">{{ $language_resource['selectvehicles'] }}</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label>{{ $language_resource['sensor'] }}</label>
                                                                <select class="form-control alerts_device" name="alert_sensor" id="sensor_list" disabled>
                                                                    <option value="0">{{ $language_resource['choose-sensor'] }}</option>
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <span class="text-danger">*თუ გსურთ რომ განგაში გავრცელდეს მოწყობილობაზე მიბმული ყველა სენსორისათვის, სენსორების ასარჩევი ველი დატოვეთ შეუვსებელი</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- შეტყობინებები -->
                                                <div id="step-3" class="">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <div class="col-md-12">
                                                                    <label>{{ $language_resource['alert-accompaniment'] }}: </label>
                                                                    <div class="col-md-12 padding">
                                                                        <label class="checkbox-inline m-r-20">
                                                                            <input type="checkbox" name="alert_sound" value="true">{{ $language_resource['sound_signal'] }}
                                                                        </label>
                                                                        <label class="checkbox-inline">
                                                                            <input type="checkbox" name="alert_popup" value="true">{{ $language_resource['popup-window'] }}
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>{{ $language_resource['sms'] }}:</label>
                                                                    <div class="col-md-12 padding margin">
                                                                        <label>
                                                                            <small>
                                                                                <input type="radio" name="alert_default_sms_standard" class="m-r-5" value="true" checked="">{{ $language_resource['standard'] }}
                                                                            </small>
                                                                        </label>
                                                                    </div>
                                                                    <div class="col-md-12 padding margin">
                                                                        <label>
                                                                            <small>
                                                                                <input type="radio" name="alert_default_sms_standard" class="m-r-5 sms_radio" value="false">{{ $language_resource['template'] }}
                                                                            </small>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group sms_textarea_form_group">
                                                                    <div class="col-md-12 padding">
                                                                        <label><i class="fa fa-wrench"></i> {{ $language_resource['constructor'] }}</label>
                                                                        <textarea class="form-control" rows="2" name="alert_sms_text"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>{{ $language_resource['sms-recipients'] }}:</label>
                                                                    <div class="col-md-12 padding margin">
                                                                        <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                            <select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">
                                                                                <option value="995">+995</option>
                                                                                <option value="374">+374</option>
                                                                            </select>
                                                                            <input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX">
                                                                            <div class="input-group-btn"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 padding margin">
                                                                        <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                            <select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">
                                                                                <option value="995">+995</option>
                                                                                <option value="374">+374</option>
                                                                            </select>
                                                                            <input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX">
                                                                            <div class="input-group-btn"></div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 padding margin">
                                                                        <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                            <select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">
                                                                                <option value="995">+995</option>
                                                                                <option value="374">+374</option>
                                                                            </select>
                                                                            <input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX">
                                                                            <div class="input-group-btn"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label>{{ $language_resource['email'] }}:</label>
                                                                    <div class="col-md-12 padding margin">
                                                                        <label>
                                                                            <small>
                                                                                <input type="radio" name="alert_default_email_standard" class="m-r-5" value="true" checked="">{{ $language_resource['standard'] }}
                                                                            </small>
                                                                        </label>
                                                                    </div>
                                                                    <div class="col-md-12 padding margin">
                                                                        <label>
                                                                            <small>
                                                                                <input type="radio" name="alert_default_email_standard" class="m-r-5 email_radio" value="false">{{ $language_resource['template'] }}
                                                                            </small>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group email_textarea_form_group">
                                                                    <div class="col-md-12 padding">
                                                                        <label><i class="fa fa-wrench"></i> {{ $language_resource['constructor'] }}</label>
                                                                        <textarea class="form-control" rows="2" name="alert_email_text"></textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group">
                                                                    <label>{{ $language_resource['email-recipients'] }}:</label>
                                                                    <div class="col-md-12 padding margin">
                                                                        <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                            <input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="{{ $language_resource['email'] }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 padding margin">
                                                                        <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                            <input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="{{ $language_resource['email'] }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-12 padding margin">
                                                                        <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                            <input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="{{ $language_resource['email'] }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- განრიგი -->
                                                <div id="step-4" class="">
                                                    <div class="row">
                                                        <div class="col-md-4 padding">
                                                            <div class="col-md-6">
                                                                <select class="form-control" name="alert_date_from">
                                                                    <option></option>
                                                                    <?php
                                                                    $start = 0;
                                                                    $end   = 23;
                                                                    for($i = $start; $i <= $end; $i++)
                                                                    {
                                                                        if($i<10){
                                                                            $hour = '0'.$i;
                                                                        }else{
                                                                            $hour = $i;
                                                                        }
                                                                        echo '<option value="'.$hour.':00:00">'.$hour.':00</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <select class="form-control" name="alert_date_to">
                                                                    <option></option>
                                                                    <?php
                                                                    $start = 0;
                                                                    $end   = 23;
                                                                    for($i = $start; $i <= $end; $i++)
                                                                    {
                                                                        if($i<10){
                                                                            $hour = '0'.$i;
                                                                        }else{
                                                                            $hour = $i;
                                                                        }
                                                                        echo '<option value="'.$hour.':00:00">'.$hour.':00</option>';
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-8">
                                                            <div class="weekDays-selector">
                                                                <input type="checkbox" id="weekday-mon" class="weekday" name="weekday[mon]" checked/>
                                                                <label for="weekday-mon">{{ $language_resource['mond'] }}</label>
                                                                <input type="checkbox" id="weekday-tue" class="weekday" name="weekday[tue]" checked/>
                                                                <label for="weekday-tue">{{ $language_resource['thud'] }}</label>
                                                                <input type="checkbox" id="weekday-wed" class="weekday" name="weekday[wed]" checked/>
                                                                <label for="weekday-wed">{{ $language_resource['wedd'] }}</label>
                                                                <input type="checkbox" id="weekday-thu" class="weekday" name="weekday[thu]" checked/>
                                                                <label for="weekday-thu">{{ $language_resource['thed'] }}</label>
                                                                <input type="checkbox" id="weekday-fri" class="weekday" name="weekday[fri]" checked/>
                                                                <label for="weekday-fri">{{ $language_resource['fryd'] }}</label>
                                                                <input type="checkbox" id="weekday-sat" class="weekday" name="weekday[sat]" checked/>
                                                                <label for="weekday-sat">{{ $language_resource['sutd'] }}</label>
                                                                <input type="checkbox" id="weekday-sun" class="weekday" name="weekday[sun]" checked/>
                                                                <label for="weekday-sun">{{ $language_resource['sund'] }}</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- მომხმარებელი -->
                                                <div id="step-5" class="">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="form-group">
                                                                <label>{{ $language_resource['users'] }}</label>
                                                                <select class="multiselect employers_multiselect" multiple="multiple" name="employers[]" id="employers_multiselect">
                                                                    @foreach($employers as $employer)
                                                                        <option value="{{ $employer -> nickname }}">{{ $employer -> nickname }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 table-container">
                <table class="table table-stripped" id="allerts_table">
                    <tr>
                        <td>{{ $language_resource['alert-name'] }}</td>
                        <td class="center">{{ $language_resource['alerttype'] }}</td>
                        <td class="center">{{ $language_resource['device'] }}</td>
                        <td class="center">{{ $language_resource['users'] }}</td>
                        <td class="center">{{ $language_resource['sound_signal'] }}</td>
                        <td class="center">{{ $language_resource['popup-window'] }}</td>
                        <td class="td_buttons">{{ $language_resource['edit'] }}</td>
                        <td class="td_buttons">{{ $language_resource['delete'] }}</td>
                    </tr>
                    @foreach($alerts as $alert)
                        @if($alert ->id != 7550)
                        <tr data-alert-id="{{ $alert -> id }}">
                            <td>{{ $alert -> name }}</td>
                            <td class="center">{{ $alert -> alert_type -> description }}</td>
                            <td class="center">{{ $alert -> device -> device -> name }}</td>
                            <td class="center">{{ $alert -> users -> count() }}</td>
                            <td class="center">
                                @if($alert -> sound == 1)
                                    {{ $language_resource['yes'] }}
                                @else
                                    {{ $language_resource['no'] }}
                                @endif
                            </td>
                            <td class="center">
                                @if($alert -> popup == 1)
                                    {{ $language_resource['yes'] }}
                                @else
                                    {{ $language_resource['no'] }}
                                @endif
                            </td>
                            <td class="td_buttons">
                                <button class="btn btn-primary edit_alert" id="{{ $alert -> id }}" data-toggle="modal" data-target="#edit_alert_{{ $alert -> id }}">{{ $language_resource['edit'] }}</button>
                                <div class="modal fade" id="edit_alert_{{ $alert -> id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="myModalLabel">{{ $alert -> name }} <small>[{{ $language_resource['edit'] }}]</small></h4>
                                            </div>
                                            <div class="modal-body">
                                                <form class="edit_warehouse_room_sensor_alert_form" id="edit_warehouse_room_sensor_alert_form_{{ $alert -> id }}" method="POST" class="alerts_target">
                                                    @csrf
                                                    <input type="hidden" name="alert_id" value="{{ $alert -> id }}">
                                                    <div id="smartwizardedit" class="smartwizard smartwizardedit" data-alert-id="{{ $alert -> id}}">
                                                        <ul>
                                                            <li><a href="#step-1"><small>{{ $language_resource['configuration'] }}</small></a></li>
                                                            <li><a href="#step-2"><small>{{ $language_resource['devices'] }}</small></a></li>
                                                            <li><a href="#step-3"><small>{{ $language_resource['notifications'] }}</small></a></li>
                                                            <li><a href="#step-4"><small>{{ $language_resource['schedule'] }}</small></a></li>
                                                            <li><a href="#step-5"><small>{{ $language_resource['users'] }}</small></a></li>
                                                        </ul>
                                                        <div>
                                                            <!-- კონფიგურაცია -->
                                                            <div id="step-1">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>{{ $language_resource['desname'] }}</label>
                                                                            <input type="text" class="form-control" name="alert_name" value="{{ $alert -> name }}" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>{{ $language_resource['alarm'] }}</label>
                                                                            <select class="form-control" name="alert_type">
                                                                                @if($alert -> value1 == 1)
                                                                                    <option value="1">{{ $language_resource['on-temp-rise'] }}</option>
                                                                                    <option value="0">{{ $language_resource['on-temp-decrease'] }}</option>
                                                                                @else
                                                                                    <option value="0">{{ $language_resource['on-temp-decrease'] }}</option>
                                                                                    <option value="1">{{ $language_resource['on-temp-rise'] }}</option>
                                                                                @endif
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row mt-3">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>{{ $language_resource['bound'] }} (°C):</label>
                                                                            <input type="number" class="form-control" name="alert_limit" required value="{{ $alert -> value2 }}">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label>{{ $language_resource['exceeding-allowed-time'] }} ({{ $language_resource['minute'] }}):</label>
                                                                            <input type="number" class="form-control" name="alert_time" min="1" step="1" max="60" required value="{{ $alert -> value3 }}">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- მოწყობილობა -->
                                                            <div id="step-2">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            {{ $alert -> sensor['controller']['warehouse']['name'] . ' - ' . $alert -> sensor['device']['name'] . ' - ' .  $alert -> sensor['name'] }}
                                                                        </div>
                                                                        <input type="hidden" name="alert_imei_old" value="{{ $alert -> device -> imei }}">
                                                                        <input type="hidden" name="alert_index_old" value="{{ $alert -> sensor['index'] }}">
                                                                        <div class="form-group">
                                                                            <label>{{ $language_resource['warehouse'] }}</label>
                                                                            <select class="form-control alerts_warehouse" name="alert_warehouse" id="warehouse_list_edit" data-alert-id="{{ $alert -> id }}">
                                                                                <option value="0">{{ $language_resource['warehouse_select'] }}</option>
                                                                                @foreach($warehouses as $warehouse)
                                                                                    <option value="{{ $warehouse -> id }}">{{ $warehouse -> name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>{{ $language_resource['device'] }}</label>
                                                                            <select class="form-control alerts_device" name="alert_device" id="device_list_{{ $alert -> id }}" data-alert-id="{{ $alert -> id }}" disabled>
                                                                                <option value="0">{{ $language_resource['selectvehicles'] }}</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label>{{ $language_resource['sensor'] }}</label>
                                                                            <select class="form-control alerts_device" name="alert_sensor" id="sensor_list_{{ $alert -> id }}" disabled>
                                                                                <option value="0">{{ $language_resource['choose-sensor'] }}</option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="form-group">
                                                                            <span class="text-danger">*თუ გსურთ რომ განგაში გავრცელდეს მოწყობილობაზე მიბმული ყველა სენსორისათვის, სენსორების ასარჩევი ველი დატოვეთ შეუვსებელი</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- შეტყობინებები -->
                                                            <div id="step-3" class="">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <div class="col-md-12">
                                                                                <label>{{ $language_resource['alert-accompaniment'] }}: </label>
                                                                                <div class="col-md-12 padding">
                                                                                    <label class="checkbox-inline m-r-20">
                                                                                        @if($alert -> sound == 1)
                                                                                            <input type="checkbox" name="alert_sound" value="true" checked>{{ $language_resource['sound_signal'] }}
                                                                                        @else
                                                                                            <input type="checkbox" name="alert_sound" value="true">{{ $language_resource['sound_signal'] }}
                                                                                        @endif
                                                                                    </label>
                                                                                    <label class="checkbox-inline">
                                                                                        @if($alert -> popup == 1)
                                                                                            <input type="checkbox" name="alert_popup" value="true" checked>{{ $language_resource['popup-window'] }}
                                                                                        @else
                                                                                            <input type="checkbox" name="alert_popup" value="true">{{ $language_resource['popup-window'] }}
                                                                                        @endif
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>SMS:</label>
                                                                                <div class="col-md-12 padding margin">
                                                                                    <label>
                                                                                        <small>
                                                                                            @if($alert -> defaultsms == null)
                                                                                                <input type="radio" name="alert_default_sms_standard" class="m-r-5" value="true" checked="">{{ $language_resource['standard'] }}
                                                                                            @else
                                                                                                <input type="radio" name="alert_default_sms_standard" class="m-r-5" value="false">{{ $language_resource['standard'] }}
                                                                                            @endif
                                                                                        </small>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="col-md-12 padding margin">
                                                                                    <label>
                                                                                        <small>
                                                                                            @if($alert -> defaultsms != null)
                                                                                                <input type="radio" name="alert_default_sms_standard" class="m-r-5 sms_radio" value="true" checked="">{{ $language_resource['template'] }}
                                                                                            @else
                                                                                                <input type="radio" name="alert_default_sms_standard" class="m-r-5 sms_radio" value="false">{{ $language_resource['template'] }}
                                                                                            @endif
                                                                                        </small>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group sms_textarea_form_group">
                                                                                <div class="col-md-12 padding">
                                                                                    <label><i class="fa fa-wrench"></i> {{ $language_resource['constructor'] }}</label>
                                                                                    <textarea class="form-control" rows="2" name="alert_sms_text">
                                                                                    @if($alert -> defaultsms != null)
                                                                                            {{ $alert -> defaultsms }}
                                                                                        @endif
                                                                                </textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label>{{ $language_resource['sms-recipients'] }}:</label>
                                                                                @foreach($alert -> phones as $phone)
                                                                                    <div class="col-md-12 padding margin">
                                                                                        <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                                            <select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">
                                                                                                @if(substr($phone -> telephon,0,3) == '995')
                                                                                                    <option value="995">+995</option>
                                                                                                    <option value="374">+374</option>
                                                                                                @else
                                                                                                    <option value="374">+374</option>
                                                                                                    <option value="995">+995</option>
                                                                                                @endif
                                                                                            </select>
                                                                                            <input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX" value="{{ substr($phone -> telephon,3) }}">
                                                                                            <div class="input-group-btn"></div>
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                                @if($alert -> phones -> count() < 3)
                                                                                    @for($i = $alert -> phones -> count(); $i < 3; $i++)
                                                                                        <div class="col-md-12 padding margin">
                                                                                            <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                                                <select name="alert_phones[code][]" class="form-control input-sm p-r-0-i valid" style="width:70px;" aria-invalid="false">
                                                                                                    <option value="995">+995</option>
                                                                                                    <option value="374">+374</option>
                                                                                                </select>
                                                                                                <input type="text" name="alert_phones[number][]" class="form-control w-auto alert-phone" maxlength="13" placeholder="5XXXXXXXX">
                                                                                                <div class="input-group-btn"></div>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endfor
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <div class="form-group">
                                                                                <label>{{ $language_resource['email'] }}:</label>
                                                                                <div class="col-md-12 padding margin">
                                                                                    <label>
                                                                                        <small>
                                                                                            @if($alert -> defaultemail == null)
                                                                                                <input type="radio" name="alert_default_email_standard" class="m-r-5" value="true" checked="">{{ $language_resource['standard'] }}
                                                                                            @else
                                                                                                <input type="radio" name="alert_default_email_standard" class="m-r-5" value="false">{{ $language_resource['standard'] }}
                                                                                            @endif
                                                                                        </small>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="col-md-12 padding margin">
                                                                                    <label>
                                                                                        <small>
                                                                                            @if($alert -> defaultemail != null)
                                                                                                <input type="radio" name="alert_default_email_standard" class="m-r-5 email_radio" value="true" checked="">{{ $language_resource['template'] }}
                                                                                            @else
                                                                                                <input type="radio" name="alert_default_email_standard" class="m-r-5 email_radio" value="false">{{ $language_resource['template'] }}
                                                                                            @endif
                                                                                        </small>
                                                                                    </label>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group email_textarea_form_group">
                                                                                <div class="col-md-12 padding">
                                                                                    <label><i class="fa fa-wrench"></i> {{ $language_resource['constructor'] }}</label>
                                                                                    <textarea class="form-control" rows="2" name="alert_email_text">
                                                                                    @if($alert -> defaultemail != null)
                                                                                            {{ $alert -> defaultemail }}
                                                                                        @endif
                                                                                </textarea>
                                                                                </div>
                                                                            </div>
                                                                            <div class="form-group">
                                                                                <label>{{ $language_resource['email-recipients'] }}:</label>
                                                                                @foreach($alert -> emails as $email)
                                                                                    <div class="col-md-12 padding margin">
                                                                                        <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                                            <input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="{{ $language_resource['email'] }}" value="{{ $email -> email }}">
                                                                                        </div>
                                                                                    </div>
                                                                                @endforeach
                                                                                @if($alert -> emails -> count() < 3)
                                                                                    @for($i = $alert -> emails -> count(); $i < 3; $i++)
                                                                                        <div class="col-md-12 padding margin">
                                                                                            <div class="input-group input-group-sm m-t-5 sms-receiver display-flex">
                                                                                                <input type="email" name="alert_emails[]" maxlength="100" class="form-control" placeholder="{{ $language_resource['email'] }}">
                                                                                            </div>
                                                                                        </div>
                                                                                    @endfor
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- ედიტის განრიგი ჩასასწორებელი -->
                                                            <div id="step-4" class="">
                                                                <div class="row">
                                                                    <div class="col-md-4 padding">
                                                                        <div class="col-md-6">
                                                                            <select class="form-control" name="alert_date_from">
                                                                                <?php
                                                                                if(isset($alert->time->timefrom)) {
                                                                                ?>
                                                                                <option value="{{ $alert -> time -> timefrom }}">{{ substr($alert -> time -> timefrom,0,5)}}</option>
                                                                                <?php
                                                                                echo '';
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
                                                                                        echo '<option value="'.$hour.':00:00">'.$hour.':00</option>';
                                                                                    }
                                                                                }
                                                                                } else {
                                                                                    echo "<option></option>";
                                                                                    $start = 0;
                                                                                    $end   = 23;
                                                                                    for($i = $start; $i <= $end; $i++)
                                                                                    {
                                                                                        if($i<10){
                                                                                            $hour = '0'.$i;
                                                                                        }else{
                                                                                            $hour = $i;
                                                                                        }
                                                                                        echo '<option value="'.$hour.':00:00">'.$hour.':00</option>';
                                                                                    }
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <select class="form-control" name="alert_date_to">
                                                                                <?php
                                                                                if(isset($alert->time->timeto)) {
                                                                                ?>
                                                                                <option value="{{ $alert -> time -> timeto }}">{{ substr($alert -> time -> timeto,0,5)}}</option>
                                                                                <?php
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
                                                                                            echo '<option value="'.$hour.':00:00">'.$hour.':00</option>';
                                                                                        }
                                                                                    }
                                                                                }
                                                                                } else {
                                                                                    echo '<option></option>';
                                                                                    $start = 0;
                                                                                    $end   = 23;
                                                                                    for($i = $start; $i <= $end; $i++)
                                                                                    {
                                                                                        if($i<10){
                                                                                            $hour = '0'.$i;
                                                                                        }else{
                                                                                            $hour = $i;
                                                                                        }
                                                                                        echo '<option value="'.$hour.':00:00">'.$hour.':00</option>';
                                                                                    }
                                                                                }
                                                                                ?>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="weekDays-selector">
                                                                            <?php
                                                                            if(isset($alert -> time -> weekdays)) {
                                                                                $weekdays_array = array_reverse(str_split(decbin($alert -> time -> weekdays)));
                                                                                foreach($weekdays_array as $key => $value)
                                                                                {
                                                                                    if($key == 0)
                                                                                    {
                                                                                        $day_key  = 'mon';
                                                                                        $day_name = $language_resource['mond'];
                                                                                    }
                                                                                    elseif ($key == 1) {
                                                                                        $day_key  = 'tue';
                                                                                        $day_name = $language_resource['thud'] ;
                                                                                    }
                                                                                    elseif ($key == 2) {
                                                                                        $day_key  = 'wed';
                                                                                        $day_name = $language_resource['wedd'];
                                                                                    }
                                                                                    elseif ($key == 3) {
                                                                                        $day_key  = 'thu';
                                                                                        $day_name = $language_resource['thed'];
                                                                                    }
                                                                                    elseif ($key == 4) {
                                                                                        $day_key  = 'fri';
                                                                                        $day_name = $language_resource['fryd'];
                                                                                    }
                                                                                    elseif ($key == 5) {
                                                                                        $day_key  = 'sat';
                                                                                        $day_name = $language_resource['sutd'];
                                                                                    }
                                                                                    elseif ($key == 6) {
                                                                                        $day_key  = 'sun';
                                                                                        $day_name = $language_resource['sund'];
                                                                                    }
                                                                                    if($value == 1)
                                                                                    {
                                                                                        $checker = 'checked';
                                                                                    }else{
                                                                                        $checker = '';
                                                                                    }
                                                                                    echo '<input type="checkbox" id="weekday-'.$day_key.'" class="weekday2" name="edit_weekday['.$day_key.']" '.$checker.'/>';
                                                                                    echo '<label for="edit_weekday-'.$day_key.'" >'.$day_name.'</label>';
                                                                                }
                                                                            } else {
                                                                            ?>
                                                                            <input type="checkbox" id="edit_weekday-mon" class="weekday" name="edit_weekday[mon]" checked/>
                                                                            <label for="edit_weekday-mon">{{ $language_resource['mond'] }}</label>
                                                                            <input type="checkbox" id="edit_weekday-tue" class="weekday" name="edit_weekday[tue]" checked/>
                                                                            <label for="edit_weekday-tue">{{ $language_resource['thud'] }}</label>
                                                                            <input type="checkbox" id="edit_weekday-wed" class="weekday" name="edit_weekday[wed]" checked/>
                                                                            <label for="edit_weekday-wed">{{ $language_resource['wedd'] }}</label>
                                                                            <input type="checkbox" id="edit_weekday-thu" class="weekday" name="edit_weekday[thu]" checked/>
                                                                            <label for="edit_weekday-thu">{{ $language_resource['thed'] }}</label>
                                                                            <input type="checkbox" id="edit_weekday-fri" class="weekday" name="edit_weekday[fri]" checked/>
                                                                            <label for="edit_weekday-fri">{{ $language_resource['fryd'] }}</label>
                                                                            <input type="checkbox" id="edit_weekday-sat" class="weekday" name="edit_weekday[sat]" checked/>
                                                                            <label for="edit_weekday-sat">{{ $language_resource['sutd'] }}</label>
                                                                            <input type="checkbox" id="edit_weekday-sun" class="weekday" name="edit_weekday[sun]" checked/>
                                                                            <label for="edit_weekday-sun">{{ $language_resource['sund'] }}</label>
                                                                            <?php
                                                                            }
                                                                            ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- მომხმარებლები -->
                                                            <div id="step-5" class="">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group">
                                                                            <label>{{ $language_resource['users'] }}</label>

                                                                            <div class="form-check">
                                                                                <input type="checkbox" class="form-check-input edit-user" id="edit_user_check_all"/>
                                                                                <label for="edit_user_check_all">ყველა</label>
                                                                            </div>

                                                                            @foreach($employers as $employer)
                                                                                <div class="form-check">
                                                                                    <input type="checkbox" class="form-check-input edit-user" id="edit_user_{{ $employer -> nickname }}" name="employers[{{ $employer -> nickname }}]" />
                                                                                    <label for="edit_user_{{ $employer -> nickname }}">{{ $employer -> nickname }}</label>
                                                                                </div>
                                                                            @endforeach

                                                                            <?php
                                                                            // <select class="multiselect employers_multiselect" multiple="multiple" name="employers[]" id="employers_multiselect">
                                                                            //     @foreach($employers as $employer)
                                                                            //         @if($alert -> users) {
                                                                            //             @foreach($alert -> users as $user)
                                                                            //                 @if($employer -> nickname == $user -> username)
                                                                            //                     <option value="{{ $user -> username }}">{{ $user -> username }}</option>
                                                                            //                 @else
                                                                            //                     <option value="{{ $employer -> nickname}}">{{ $employer -> nickname }}</option>
                                                                            //                 @endif
                                                                            //             @endforeach
                                                                            //         }
                                                                            //         @else
                                                                            //             <option value="{{ $employer -> nickname}}">დამატებითები</option>
                                                                            //         @endif
                                                                            //     @endforeach
                                                                            // </select>
                                                                            ?>


                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="td_buttons">
                                <button class="btn btn-danger" data-toggle="modal" data-target="#delete_alert_{{ $alert -> id }}">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </button>
                                <div class="modal fade" id="delete_alert_{{ $alert -> id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                    <div class="modal-dialog" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                <h4 class="modal-title" id="myModalLabel">{{ $language_resource['delete'] }}</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>{{ $language_resource['deleteconf'] }}?</p>
                                            <!-- <a href="/administration/delete-alert/{{ $alert -> id }}"> -->
                                                <button class="btn btn-danger confirm-delete-alert" data-alert="{{ $alert -> id }}"><span class="glyphicon glyphicon-trash"></span></button>
                                                <!-- </a> -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @endforeach
                </table>
            </div>
        </div>
    </div>

    @include('footer')
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
    <link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard.min.css" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/smart_wizard_theme_dots.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/bbbootstrap/libraries@main/jquery.smartWizard.min.js"></script>
    <script type="text/javascript" src="{{ url('/js/bootstrap-multiselect.js') }}"></script>
    <script type="text/javascript" src="{{ url('/js/alerts.js') }}"></script>
    <script type="text/javascript" src="{{ url('/js/administration_warehouse_room_sensors_alerts.js') }}"></script>
@endsection