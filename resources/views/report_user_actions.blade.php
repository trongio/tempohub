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
	            					<label>{{ $language_resource['user'] }}</label>
	            					<select class="form-control" name="user_nickname" id="user_action_select">
	            						<option value="0">{{ $language_resource['user'] }}</option>
	            						@foreach($users as $usr)
	            							<option value="{{ $usr -> nickname }}">{{ $usr -> nickname }}</option>
	            						@endforeach
	            					</select>
	            				</div>

								<div class="form-group">
	            					<label>{{ $language_resource['user'] }}</label>
	            					<select class="form-control" name="user_nickname" id="user_action_select">
	            						<option value="0">{{ $language_resource['user'] }}</option>
	            						@foreach($users as $usr)
	            							<option value="{{ $usr -> nickname }}">{{ $usr -> nickname }}</option>
	            						@endforeach
	            					</select>
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
	            			<td>{{ $language_resource['id'] }}</td>
	            			<td>{{ $language_resource['user'] }}</td>
	            			<td>{{ $language_resource['action'] }}</td>
	            			<td>{{ $language_resource['datecreated'] }}</td>
	            		</tr>
	            		@foreach($user_actions as $user_action)
	            			<tr>
	            				<td>{{ $user_action -> id }}</td>
	            				<td>{{ $user_action -> nickname }}</td>
	            				<td>{{ $user_action -> action_name }}</td>
	            				<td>{{ $user_action -> created_at }}</td>
	            			</tr>
	            		@endforeach
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