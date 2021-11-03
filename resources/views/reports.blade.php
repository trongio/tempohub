@extends('master')

@section('body')
    @include('header')

    <div class="container-fluid reports_container_fluid">
        <div class="col-md-12 reports_main_container">
	        <div class="col-md-12 row reports_header">
	            @include('report_sub_menu')
	        </div>
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 reports_filters_container">
				<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 filter-menu-div">

					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="reporttype" id="temperature" value="temperature" checked="checked">
						<label class="form-check-label" for="temperature">{{ $language_resource['temperature'] }}</label>&nbsp;

						<input class="form-check-input" type="radio" name="reporttype" id="temp_range" value="temp_range">
						<label class="form-check-label" for="temp_range">{{ $language_resource['temp-range'] }}</label>
					</div>

				</div>
				<div class="row filter-div">
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 filter-input">
						<div class="form-group">
							<label for="warehouse_id">{{ $language_resource['warehouse'] }}</label>
							<select class="form-control" name="warehouseid" id="reports_warehouse_select">
							@foreach($warehouses as $warehouse)
								<option value="{{ $warehouse -> id }}">{{ $warehouse -> name }}</option>
							@endforeach
							</select>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 filter-input">
						<div class="form-group warehouse_rooms_group">
							<label for="room_id">{{ $language_resource['room'] }}</label>
							<select class="form-control" name="room_id" id="reports_room_select">
								@foreach($rooms as $room)
									<option value="{{ $room -> id }}">{{ $room -> name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-lg-2 col-md-2 col-sm-6 col-xs-12 filter-input">
						<div class="form-group warehouse_rooms_group">
							<label for="room_id">{{ $language_resource['sensor'] }}</label>
							<select class="form-control" name="sensor" id="reports_sensor_select">
								@foreach($room -> sensors as $sensor)
									<option value="{{ $room -> id }}" data-device-imei="{{ $sensor -> imei }}" data-device-index="{{ $sensor -> index }}">{{ $sensor -> name }}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class='col-lg-2 col-md-2 col-sm-6 col-xs-12'>
						<div class="form-group">
							<label for="startdate">პერიოდი დან</label>
							<div class='input-group date startdate' id='startdate'>
								<input type='text' class="form-control startdate" name="startdate" />
								<span class="input-group-addon">
               <span class="glyphicon glyphicon-calendar"></span>
               </span>
							</div>
						</div>
					</div>
					<script type="text/javascript">
						$(function () {
							$('.startdate').datetimepicker({
								format:'YYYY-MM-DD HH:mm'
							});
						});
					</script>
					<div class='col-lg-2 col-md-2 col-sm-6 col-xs-12'>
						<div class="form-group">
							<label for="enddate">პერიოდი მდე</label>
							<div class='input-group date enddate' id='enddate'>
								<input type='text' class="form-control enddate" name="enddate" />
								<span class="input-group-addon">
               <span class="glyphicon glyphicon-calendar"></span>
               </span>
							</div>
						</div>
					</div>
					<script type="text/javascript">
						$(function () {
							$('.enddate').datetimepicker({format:'YYYY-MM-DD HH:mm'});
						});
					</script>

					<div class="col-lg-1 col-md-1 col-sm-3 col-xs-6 filter-input">
						<div class="form-group">
                            <label></label>
							<button class="form-control btn btn-default generate_button" id="generate_report_submit_btn" name="submit">{{ $language_resource['generate'] }}</button>
						</div>
					</div>

					<div class="col-lg-1 col-md-1 col-sm-3 col-xs-6 filter-input">
						<div class="form-group">
                            <label></label>
							<button class="form-control btn btn-default"  id="open_range_filtr"><i class="fas fa-filter"></i></button>
						</div>
					</div>


					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 range-filter-div" id="range-filter-div">
						პარამეტრები შესათანხმებელია
					</div>
				</div>
			</div>



            <div class="col-md-12 reports_table">
				<div id="report_table_div">
					<table class="table table-striped" id="report-table-for-export">
						<thead>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</thead>
						<tbody id="report-body"><img src="/images/loading-36.gif" alt="Loading..." id="loading-image"></tbody>
					</table>
				</div>
				<div id="range_report_table_div">
					<table class="table table-striped" id="report-table-for-export_range" style="width: 100%;">
						<thead>
							<tr>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</thead>
						<tbody id="report-body"><img src="/images/loading-36.gif" alt="Loading..." id="loading-image"></tbody>
					</table>
				</div>
	        </div>



        </div>
    </div>

    @include('footer')
@endsection

@section('css')
    
@endsection

@section('js')
    <script type="text/javascript" src="{{ url('/js/reports.js') }}"></script>
@endsection