@extends('master')

@section('body')
    @include('header')

    	<div class="col-lg-12 col-dm-12 col-sm-12 col-xs-12 reports_container_fluid">
        	<div class="col-md-12 reports_main_container">
	            <div class="col-md-12 reports_header">
	            	@include('administration_sub_menu')
	            </div>
				<div class="col-md-12 col-sm-12 alert alert-success" role="alert"></div>
	            <div class="col-md-12 alerts_header_container">
            		<div class="left" id="title_div" data-warehouse-room-id="{{ $room->id }}">
						{{ $language_resource['warehouse'] }}: <a href="/administration/warehouse-rooms/{{ $room -> warehouse -> id }}"> {{ $room -> warehouse -> name }} </a> / {{ $room -> name }}
            		</div>
            		<div class="right">
            			<button class="btn btn-primary" id="open_sensor_add_modal" data-toggle="modal" data-target="#add_warehouse_room_point"><i class="far fa-plus-square"></i> {{ $language_resource['add'] }}</button>
            			<!-- წერტილის დამატება -->
						<div class="modal fade" id="add_warehouse_room_point" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
						  <div class="modal-dialog" role="document">
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						        <h4 class="modal-title" id="myModalLabel">{{ $language_resource['add'] }}</h4>
						      </div>
						      <div class="modal-body">

			      				<form id="add_warehouse_room_point_form" method="POST">
	                        		@csrf
	                        		<input type="hidden" name="roomid" value="{{ $room -> id }}">
									<div class="form-group">
										<label for="controller">{{ $language_resource['controller'] }}</label>	
										<select class="form-control add_controller" name="controller" id="add_controller">
	                        				<option value=""></option>
											@foreach($controllers as $controller)
											<option value="{{ $controller -> id }}" data-controller-id="{{ $controller -> id }}">{{ $controller -> controller_name }}</option>
											@endforeach
	                        			</select>
									</div>
									<div class="form-group">
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
												<label for="sensors">{{ $language_resource['sensor'] }}</label>
												<select class="form-control add_sensors" name="sensorid" id="add_sensors" disabled></select>
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
												<label for="sensornewname">{{ $language_resource['new'] . $language_resource['desname'] }}</label>
												<input type="text" name="sensornewname" class="form-control" />
											</div>
										</div>
									</div>
	                        		<div class="form-group">
	                        			<div class="room_plan_drawer_main_container">
											<div class="room_plan_drawer_container_add">
												<img src="/images/rooms/{{ $room->id . '/' . $room -> image }}" alt="">
						                    </div>
		                        		</div>
	                        		</div>
	                        		<div class="form-group">
										<div class="row">
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
												<label>{{ $language_resource['map'] }}_X</label>
												<input type="text" name="map_x" class="form-control" readonly id="map_x">
											</div>
											<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
												<label>{{ $language_resource['map'] }}_Y</label>
											<input type="text" name="map_y" class="form-control" readonly id="map_y">
											</div>
										</div>
	                        		</div> 
	                        		<div class="form-group">
	                        			<button class="btn btn-primary">{{ $language_resource['add'] }}</button>
	                        		</div>
	                        	</form>

						      </div>
						    </div>
						  </div>
						</div>
            		</div>
            	</div>
            	<div class="col-md-12 table-container">
            		<div class="col-md-6">
	            		<div class="col-md-12 room_plan_container_in_sensors_page">
		            		<div class="warehouse_room_container_in_sensors_page">
		            			<img src="/images/rooms/{{ $room -> id . '/' . $room -> image }}" alt="">
		            		</div>
	            		</div>
						<div class="col-md-12 map_explanation_container_in_sensors_page">
		            		<ul>
								<li><div class="explanateion_pin active-pin">1</div> - {{ $language_resource['pointactivesensor'] }}.</li>
								<li><div class="explanateion_pin notactive-pin">1</div> - {{ $language_resource['pointinactivesensor'] }}.</li>
								<li><div class="explanateion_pin notactive-pin">-</div> - {{ $language_resource['freepoint'] }}.</li>
							</ul>
	            		</div>
	            	</div>
	            	<div class="col-md-6 table-container">
	            		<table class="table table-striped" id="warehouse-room-sensors-table">
	            			<tr>
								<th>{{ $language_resource['controller'] }}</th>
	            				<th>{{ $language_resource['sensor'] }}</th>
	            				<th>{{ $language_resource['index'] }}</th>
	            				<th class="td_counts">{{ $language_resource['status'] }}</th>
	            				<th class="td_buttons">{{ $language_resource['settings'] }}</th>
	            				<th class="td_counts">{{ $language_resource['action'] }}</th>
	            			</tr>
	            			@foreach($roompins as $sensor)
		            			<tr class="point-row" data-pin-checked="{{ $sensor -> roomsensorid }}">
									@if($sensor -> sensorid) 
										<td>{{ $sensor -> controller_name }}</td>
										<td>{{ $sensor -> name }}</td>
										<td>{{ $sensor -> index }}</td>
										<td class="td_counts">
											@if($sensor -> isactive == true)
												{{ $language_resource['aktiv'] }}
											@else
												{{ $language_resource['inactive'] }}
											@endif
										</td>
										<td class="td_buttons">
											<a href="/administration/alerts">
												<button class="btn btn-sm btn-default">
													<i class="fas fa-satellite-dish"></i>
												</button>
											</a>
										</td>
									@else 
										<td colspan="5">{{ $language_resource['sensornotatached'] }}</td>
									@endif
		            				<td class="td_buttons">
										<div class="form-group">
											@if($sensor -> sensorid)
												<button class="btn btn-sm btn-warning" data-toggle="modal" data-target="#unlink_sensor_{{ $sensor -> id }}" title="{{ $language_resource['detach'] }}">
													<i class="fas fa-unlink"></i>
												</button>
											@else
												<button class="btn btn-sm btn-warning link-sensor-btn" data-toggle="modal" data-sensor-roomsensorid="{{ $sensor -> roomsensorid }}" data-room-id="{{ $sensor -> roomid }}" data-target="#link_sensor_{{ $sensor -> roomsensorid }}" title="{{ $language_resource['attach'] }}">
													<i class="fas fa-link"></i>
												</button>
											@endif
											<button class="btn btn-sm btn-danger" data-toggle="modal" data-target="#delete_point_{{ $sensor -> roomsensorid }}" title="{{ $language_resource['delete'] }}">
												<span class="glyphicon glyphicon-trash"></span>
											</button>
										</div>

										<!-- ცარიელ წერტილზე სენსორის მიბმა -->
										<div class="modal fade" id="link_sensor_{{ $sensor -> roomsensorid }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
											<div class="modal-dialog" role="document">
												<div class="modal-content">
													<div class="modal-header">
														<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
														<h4 class="modal-title" id="myModalLabel">{{ $language_resource['add'] }}</h4>
													</div>
													<div class="modal-body">
														<form id="link-sensor-2-point-form_{{ $sensor->roomsensorid }}" method="POST">
															@csrf
															<input type="hidden" name="roomsensorid" value="{{ $sensor -> roomsensorid }}">
															<div class="form-group">
																<label for="controller">{{ $language_resource['controller'] }}</label>	
																<select class="form-control add_controller" name="controller" id="add_controller">
																	<option value=""></option>
																	@foreach($controllers as $controller)
																	<option value="{{ $controller -> id }}">{{ $controller -> controller_name }}</option>
																	@endforeach
																</select>
															</div>
															<div class="form-group">
																<div class="row">
																	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
																		<label for="sensors">{{ $language_resource['sensor'] }}</label>
																		<select class="form-control add_sensors" name="sensorid" id="add_sensors" disabled></select>
																	</div>
																	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
																		<label for="sensornewname">{{ $language_resource['new'] . $language_resource['desname'] }}</label>
																		<input type="text" name="sensornewname" class="form-control" />
																	</div>
																</div>
															</div>
															<div class="form-group">
																<div class="room_plan_drawer_main_container">
																	<div class="room_plan_drawer_container_edit" id="room_plan_drawer_container_link_{{ $sensor -> roomsensorid }}" data-map-id="{{ $sensor->roomsensorid}}">
																		<img src="/images/rooms/{{ $room->id . '/' . $room -> image }}" alt="">
																	</div>
																</div>
															</div>
															<div class="form-group">
																<div class="row">
																	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
																		<label>{{ $language_resource['map'] }}_X</label>
																		<input type="text" name="map_x" class="form-control" readonly id="map_x">
																	</div>
																	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
																		<label>{{ $language_resource['map'] }}_Y</label>
																		<input type="text" name="map_y" class="form-control" readonly id="map_y">
																	</div>
																</div>
															</div> 
															<div class="form-group">
																<button class="btn btn-primary link-sensor-2-point-btn" data-point-id="{{ $sensor->roomsensorid }}">{{ $language_resource['add'] }}</button>
															</div>
														</form>
													</div>
												</div>
											</div>
										</div>
		            					
										<!-- წერტილის წაშლა -->
			            				<div class="modal fade" id="delete_point_{{ $sensor -> roomsensorid }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
											<div class="modal-dialog" role="document">
												<div class="modal-content">
												<div class="modal-header">
													<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
													<h4 class="modal-title" id="myModalLabel">{{ $language_resource['delete'] }}</h4>
												</div>
												<div class="modal-body">
													<p>{{ $language_resource['deleteconf'] }}</p>
													<!-- <a href="/administration/delete-warehouse-room-sensor/{{ $sensor -> roomsensorid }}"> -->
														<button class="btn btn-danger confirm-delete-point" data-point-id="{{ $sensor -> roomsensorid }}">
															<span class="glyphicon glyphicon-trash"></span>
														</button>
													<!-- </a> -->
												</div>
												</div>
											</div>
										</div>

										<!-- წერტილიდან სენსორის ჩახსნა -->
										<div class="modal fade" id="unlink_sensor_{{ $sensor -> id }}" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
										  <div class="modal-dialog" role="document">
										    <div class="modal-content">
										      <div class="modal-header">
										        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
										        <h4 class="modal-title" id="myModalLabel">{{ $language_resource['delete'] }}</h4>
										      </div>
										      <div class="modal-body">
					      							<p>
														{{ $language_resource['warningdetach'] }}
								      				</p>
								      				<!-- <a href="/administration/unlink-warehouse-room-sensor/{{ $sensor -> id }}"> -->
								      					<button class="btn btn-warning confirm-unlink-sensor" data-point-id="{{ $sensor -> id }}">
															<i class="fas fa-unlink"></i>
								      					</button>
				            						<!-- </a> -->
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
	    </div>
    @include('footer')
@endsection

@section('css')
    
@endsection

@section('js')
<script type="text/javascript" src="{{ url('/js/administration_warehouses.js') }}"></script>
<script type="text/javascript" src="{{ url('/js/administration_warehouse_room_sensors.js') }}"></script>
@endsection