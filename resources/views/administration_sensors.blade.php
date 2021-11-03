@extends('master')

@section('body')
    @include('header')
    	<div class="container-fluid reports_container_fluid">
        	<div class="col-md-12 reports_main_container">
	            <div class="col-md-12 reports_header">
	            	@include('administration_sub_menu')
	            </div>
	            <div class="col-md-12 alerts_header_container">
            	</div>
            	<div class="col-md-12 table-container">
            		<table class="table table-striped">
            			<tr>
            				<th>{{ $language_resource['stock'] }}</th>
            				<th>{{ $language_resource['room'] }}</th>
            				<th>{{ $language_resource['controller'] }}</th>
            				<th>{{ $language_resource['sensor'] }}</th>
            				<th>{{ $language_resource['status'] }}</th>
            			</tr>
						@foreach($sensors as $sensor)
							<tr>
								<td>{{ $sensor -> warehouse_name }}</td>
								<td>{{ $sensor -> room_name }}</td>
								<td>{{ $sensor -> controller_name }}</td>
								<td>{{ $sensor -> sensor_name }}</td>
								@if($sensor -> isactive == true) 
									<td>აქტიური</td>
								@else
									<td>არააქტიური</td>
								@endif
							</tr>
						@endforeach
						
            		</table>
            	</div>
	        </div>
	    </div>
    @include('footer')
@endsection