@extends('master')

@section('body')
    @include('header')
        <div class="container-fluid reports_container_fluid">
        	<div class="col-md-12 reports_main_container">
	            <div class="col-md-12 reports_header">
	            	@include('report_sub_menu')
	            	<div class="col-md-12 reports_filters_container">
	            		<div class="report_filters">
	            			<form action="" method="" class="form-inline">
	            				<div class="form-group">
	            					<label>{{ $language_resource['startdate'] }}</label>
	            					<input type="date" name="start_date" class="form-control">
	            				</div>
	            				<div class="form-group">
	            					<label>{{ $language_resource['end_date'] }}</label>
	            					<input type="date" name="end_date" class="form-control">
	            				</div>

	            				<div class="form-group">
	            					<button class="btn btn-default generate_button" name="submit">{{ $language_resource['generate'] }}</button>
	            				</div>
	            			</form>
	            		</div>
	            		<div class="report_exports">
	            			<div class="col-md-6">
	            				<button class="btn btn-default"><i class="fas fa-file-excel"></i> {{ $language_resource['excel'] }}</button>
	            			</div>
	            			<div class="col-md-6">
	            				<button class="btn btn-default"><i class="fas fa-file-pdf"></i> {{ $language_resource['pdf'] }}</button>
	            			</div>
	            		</div>
	            	</div>
	            </div>
	            <div class="col-md-12 reports_table">
	            	<table class="table table-striped">
	            		<tr>
	            			<td>{{ $language_resource['alert-name'] }}</td>
	            			<td>{{ $language_resource['warehouse_name'] }}</td>
	            			<td>{{ $language_resource['room_name'] }}</td>
	            			<td>{{ $language_resource['sensor_name'] }}</td>
	            			<td>{{ $language_resource['alarm'] }}</td>
	            			<td>{{ $language_resource['date'] }}</td>
	            		</tr>
	            		@for($i = 0; $i<= 30; $i++)
	            			<tr>
	            				<td>გადაჭარბება</td>
	            				<td>Test</td>
	            				<td>Room 1</td>
	            				<td>Sensor 1</td>
	            				<td>ტემპერატურა ჩამოცდა 20 გრადუსით</td>
	            				<td>03-21-2021 18:00:31</td>
	            			</tr>
	            		@endfor
	            	</table>
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