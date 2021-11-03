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
						{{ $language_resource['warehouses'] }}
            		</div>
            		<div class="right">
            			<button class="btn btn-primary" data-toggle="modal" data-target="#add_warehouse"><i class="far fa-plus-square"></i> {{ $language_resource['add'] }}</button>
            			<div class="modal fade" id="add_warehouse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						        <h4 class="modal-title" id="myModalLabel">{{ $language_resource['add'] }}</h4>
						      </div>
						      <div class="modal-body">
	                        	@csrf
	                        	<input type="hidden" name="firmaid" value="{{ $user -> firmaid }}">
	                        	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	                        	<div class="form-group">
	                        		<label>{{ $language_resource['desname'] }}</label>
	                        		<input type="text" class="form-control" name="name">
	                        	</div>
	                        	<div class="form-group">
	                        		<label>{{ $language_resource['status'] }}</label>
	                        		<select class="form-control" name="isactive" id="status">
	                        			<option value="1">{{ $language_resource['aktiv'] }}</option>
	                        			<option value="0">{{ $language_resource['inactive'] }}</option>
	                        		</select>
	                        	</div>
	                        	<div class="form-group">
	                        		<button class="btn btn-primary" id="submit_add_warehouse_button">{{ $language_resource['add'] }}</button>
	                        	</div>
						      </div>
						    </div>
						  </div>
						</div>
            		</div>
            	</div>
            	<div class="col-md-12 table-container">
            		<table class="table table-striped" id="warehouse-table">
            			<tr>
            				<th class="td_id">{{ $language_resource['id'] }}</th>
            				<th>{{ $language_resource['desname'] }}</th>
            				<th class="td_counts">{{ $language_resource['status'] }}</th>
            				<th class="td_buttons">{{ $language_resource['rooms'] }}</th>
            				<th class="td_buttons">{{ $language_resource['edit'] }}</th>
            				<th class="td_buttons">{{ $language_resource['delete'] }}</th>
            			</tr>
            			@foreach($warehouses as $warehouse)
	            			<tr data-warehouse-id="{{ $warehouse->id }}">
	            				<td class="td_id">{{ $warehouse -> id }}</td>
	            				<td>{{ $warehouse -> name }}</td>
	            				<td class="td_counts">
	            					@if($warehouse -> isactive == true)
										{{ $language_resource['aktiv'] }}
	            					@else
										{{ $language_resource['inactive'] }}
	            					@endif
	            				</td>
	            				<td class="td_buttons">
	            					<a href="/administration/warehouse-rooms/{{ $warehouse -> id }}">
		            					<button class="btn btn-default">
		            						<span class="glyphicon glyphicon-th-large"></span>
		            					</button>
		            				</a>
	            				</td>
	            				<td class="td_buttons">
	            					<button class="btn btn-primary" data-toggle="modal" data-target="#edit_warehouse_{{ $warehouse -> id }}">
	            						<span class="glyphicon glyphicon-pencil"></span>
	            					</button>
			            			<div class="modal fade" id="edit_warehouse_{{ $warehouse -> id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
									  <div class="modal-dialog" role="document">
									    <div class="modal-content">
									      <div class="modal-header">
									        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									        <h4 class="modal-title" id="myModalLabel">{{ $language_resource['edit'] }}</h4>
									      </div>
									      <div class="modal-body">
				                        		@csrf
				                        		<input type="hidden" name="firmaid" value="{{ $user -> firmaid }}">
				                        		<input type="hidden" name="id" value="{{ $warehouse -> id }}">
				                        		<div class="form-group">
				                        			<label>{{ $language_resource['desname'] }}</label>
				                        			<input type="text" class="form-control" name="name" value="{{ $warehouse -> name }}">
				                        		</div>
				                        		<div class="form-group">
				                        			<label>{{ $language_resource['status'] }}</label>
				                        			<select class="form-control" name="isactive">
				                        				@if($warehouse -> isactive == true)
				                        					<option value="1">{{ $language_resource['aktiv'] }}</option>
				                        					<option value="0">{{ $language_resource['inactive'] }}</option>
				                        				@else
				                        					<option value="0">{{ $language_resource['inactive'] }}</option>
				                        					<option value="1">{{ $language_resource['aktiv'] }}</option>
				                        				@endif
				                        			</select>
				                        		</div>
				                        		<div class="form-group">
				                        			<button class="btn btn-primary confirm-edit-warehouse" data-warehouse="{{ $warehouse -> id }}">{{ $language_resource['edit'] }}</button>
				                        		</div>
									      </div>
									    </div>
									  </div>
									</div>
	            				</td>
	            				<td class="td_buttons">
									<button class="btn btn-danger" data-toggle="modal" data-target="#delete_warehouse_{{ $warehouse -> id }}"><span class="glyphicon glyphicon-trash"></span></button>
			            			<div class="modal fade" id="delete_warehouse_{{ $warehouse -> id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
									  <div class="modal-dialog" role="document">
									    <div class="modal-content">
									      <div class="modal-header">
									        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									        <h4 class="modal-title" id="myModalLabel">{{ $language_resource['delete'] }}</h4>
									      </div>
									      <div class="modal-body">
				      							<p>{{ $language_resource['deleteconf'] }}?</p>
							      				<button class="btn btn-danger confirm-delete-warehouse" data-warehouse="{{ $warehouse -> id }}"><span class="glyphicon glyphicon-trash"></span></button>
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
    <script type="text/javascript" src="{{ url('/js/administration_warehouses.js') }}"></script>
@endsection