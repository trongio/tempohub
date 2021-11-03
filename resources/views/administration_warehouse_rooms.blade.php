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
            			{{ $language_resource['warehouse'] }}: <a href="/administration/"> {{ $warehouse -> name }}</a>
            		</div>
            		<div class="right">
            			<button class="btn btn-primary" data-toggle="modal" data-target="#add_warehouse_room"><i class="far fa-plus-square"></i> {{ $language_resource['add'] }}</button>
            			<div class="modal fade" id="add_warehouse_room" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						        <h4 class="modal-title" id="myModalLabel">{{ $language_resource['add'] }}</h4>
						      </div>
						      <div class="modal-body">
								<!-- add room form -->
			      				<form id="add_warehouse_room_form" method="POST" enctype="multipart/form-data">
	                        		@csrf
	                        		<input type="hidden" name="warehouseid" value="{{ $warehouse -> id }}">
	                        		<div class="form-group">
	                        			<label>{{ $language_resource['desname'] }}</label>
	                        			<input type="text" class="form-control" name="name">
	                        		</div>
	                        		<div class="form-group">
	                        			<label>{{ $language_resource['room_plan'] }}</label>
	                        			<input type="file" id="exampleInputFile" name="image">
	                        		</div>
	                        		<div class="form-group">
	                        			<label>{{ $language_resource['status'] }}</label>
	                        			<select class="form-control" name="isactive">
	                        			 	<option value="1">{{ $language_resource['aktiv'] }}</option>
	                        				<option value="0">{{ $language_resource['inactive'] }}</option>
	                        			</select>
	                        		</div>
	                        		<div class="form-group">
	                        			<button type="submit" class="btn btn-primary" id="submit_add_warehouse_room_button">{{ $language_resource['add'] }}</button>
	                        		</div>
	                        	</form>
						      </div>
						    </div>
						  </div>
						</div>
            		</div>
            	</div>
            	<div class="col-md-12 table-container">
            		<table class="table table-striped" id="warehouse-room-table">
            			<tr>
            				<th class="td_id">Id</th>
            				<th class="td_wh_name">{{ $language_resource['warehouse_name'] }}</th>
            				<th>{{ $language_resource['room_name'] }}</th>
            				<th class="td_counts">{{ $language_resource['status'] }}</th>
            				<th class="td_buttons">{{ $language_resource['points'] }}</th>
            				<th class="td_buttons">{{ $language_resource['edit'] }}</th>
            				<th class="td_buttons">{{ $language_resource['delete'] }}</th>
            			</tr>
            			@foreach($warehouse_rooms as $warehouse_room)
	            			<tr data-warehouse-room-id="{{ $warehouse_room -> id }}">
	            				<td class="td_id">{{ $warehouse_room -> id }}</td>
	            				<td class="td_wh_name">{{ $warehouse_room -> warehouse -> name }}</td>
	            				<td>{{ $warehouse_room -> name }}</td>
	            				<td class="td_counts">
	            					@if($warehouse_room -> isactive == true)
										{{ $language_resource['aktiv'] }}
	            					@else
										{{ $language_resource['inactive'] }}
	            					@endif
	            				</td>
	            				<td class="td_buttons">
	            					<a href="/administration/warehouse-room-sensors/{{ $warehouse_room -> id }}">
		            					<button class="btn btn-default">
											<i class="fas fa-map-marker-alt"></i>
		            					</button>
		            				</a>
	            				</td>
	            				<td class="td_buttons">
	            					<button class="btn btn-primary" data-toggle="modal" data-target="#edit_warehouse_room_{{ $warehouse_room -> id }}">
	            						<span class="glyphicon glyphicon-pencil"></span>
	            					</button>
				            		<div class="modal fade" id="edit_warehouse_room_{{ $warehouse_room -> id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
									  <div class="modal-dialog" role="document">
									    <div class="modal-content">
									      <div class="modal-header">
									        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									        <h4 class="modal-title" id="myModalLabel">{{ $language_resource['edit'] }}</h4>
									      </div>
									      <div class="modal-body">
											  <!-- edit room form -->
						      				<form id="edit_warehouse_room_form_{{ $warehouse_room -> id }}" method="POST" enctype="multipart/form-data">
				                        		@csrf
				                        		<input type="hidden" name="warehouseid" value="{{ $warehouse -> id }}">
				                        		<input type="hidden" name="id" value="{{ $warehouse_room -> id }}">
				                        		<div class="form-group">
				                        			<label>{{ $language_resource['desname'] }}</label>
				                        			<input type="text" class="form-control" name="name" value="{{ $warehouse_room -> name }}">
				                        		</div>
				                        		<div class="form-group">
				                        			<label>{{ $language_resource['room_plan'] }}</label>
				                        			<input type="file" name="image">
				                        		</div>
				                        		<div class="form-group">
				                        			<label>{{ $language_resource['status'] }}</label>
				                        			<select class="form-control" name="isactive">
														@if($warehouse_room -> isactive == true)
				                        					<option value="1">{{ $language_resource['aktiv'] }}</option>
				                        					<option value="0">{{ $language_resource['inactive'] }}</option>
				                        				@else
				                        					<option value="0">{{ $language_resource['inactive'] }}</option>
				                        					<option value="1">{{ $language_resource['aktiv'] }}</option>
				                        				@endif
				                        			</select>
				                        		</div>
				                        		<div class="form-group">
				                        			<button class="btn btn-primary submit_edit_warehouse_room_button" data-warehouse-room="{{ $warehouse_room -> id }}">{{ $language_resource['edit'] }}</button>
				                        		</div>
				                        	</form>
									      </div>
									    </div>
									  </div>
									</div>
	            				</td>
	            				<td class="td_buttons">
	            					<button class="btn btn-danger" data-toggle="modal" data-target="#delete_room_{{ $warehouse_room -> id }}">
	             						<span class="glyphicon glyphicon-trash"></span>
	            					</button>
		            				<div class="modal fade" id="delete_room_{{ $warehouse_room -> id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
									  <div class="modal-dialog" role="document">
									    <div class="modal-content">
									      <div class="modal-header">
									        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									        <h4 class="modal-title" id="myModalLabel">{{ $language_resource['delete'] }}</h4>
									      </div>
									      <div class="modal-body">
				      							<p>{{ $language_resource['deleteconf'] }}?</p>
							      				<button class="btn btn-danger confirm-delete-warehouse" data-warehouse-room-id="{{ $warehouse_room -> id }}">
							      					<span class="glyphicon glyphicon-trash"></span>
							      				</button>
									      </div>
									    </div>
									  </div>
									</div>
								</td>
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
    <script type="text/javascript" src="{{ url('/js/administration_warehouse_rooms.js') }}"></script>
@endsection