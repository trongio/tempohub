@extends('master')

@section('body')
    @include('header')

        <div class="container-fluid">
            <div class="col-md-6">
            	<div class="col-md-12 profile_settings_container padding">
            		<div class="col-md-12 settings_title_container padding">
						{{ $language_resource['profile'] }}
            		</div>
            		<div class="col-md-12 settings_container padding">
            			<div class="col-md-6">
            				<div class="col-md-12 padding">
		            			<form action="/user-profile" method="POST">
		            				@csrf
		            				<div class="form-group">
		            					<label>{{ $language_resource['username'] }}</label>
		            					<input type="text" name="nickname" class="form-control input" value="{{ $user -> nickname }}" readonly>
		            				</div>
		            				<div class="form-group">
		            					<label>{{ $language_resource['firstname'] }}</label>
		            					<input type="text" name="name" class="form-control input" value="{{ $user -> name }}">
		            				</div>
		            				<div class="form-group">
		            					<label>{{ $language_resource['lastname'] }}</label>
		            					<input type="text" name="surname" class="form-control input" value="{{ $user -> surname }}">
		            				</div>
		            				<div class="form-group">
		            					<label>{{ $language_resource['email'] }}</label>
		            					<input type="email" name="email" class="form-control input" value="{{ $user -> email }}">
		            				</div>
		            				<div class="form-group">
		            					<label>{{ $language_resource['phone'] }}</label>
		            					<input type="text" name="tel" class="form-control input" value="{{ $user -> tel }}">
		            				</div>
		            				<div class="form-group">
		            					<button class="btn btn-primary submit" name="submit">{{ $language_resource['save'] }}</button>
		            				</div>
		            			</form>
		            		</div>
	            		</div>
	            		<div class="col-md-6">
            				<div class="col-md-12 padding">
		            			<form action="/update-password" method="POST">
		            				@csrf
		            				<div class="form-group">
		            					<label>{{ $language_resource['oldpassword'] }}</label>
		            					<input type="text" name="old_password" class="form-control input">
		            				</div>
		            				<div class="form-group">
		            					<label>{{ $language_resource['confirmpassword'] }}</label>
		            					<input type="text" name="new_password" class="form-control input">
		            				</div>
		            				<div class="form-group">
		            					<button class="btn btn-primary submit" name="submit">{{ $language_resource['save'] }}</button>
		            				</div>
		            			</form>
		            		</div>
	            		</div>
            		</div>
            	</div>
            </div>
            <div class="col-md-6">
            	<div class="col-md-12 profile_settings_container padding">
            		<div class="col-md-12 settings_title_container">
						{{ $language_resource['additional_settings'] }}
            		</div>
            		<div class="col-md-12 settings_container padding">
            			<div class="col-md-6">
            				<div class="col-md-12 padding">
		            			<form action="/user-settings" method="POST">
		            				@csrf
		            				<div class="form-group">
		            					<label>{{ $language_resource['temperatureunit'] }}</label>
		            					<select class="form-control input" name="unit_temperature" disabled>
		            						<option value="C">°C</option>
		            						<option value="F">°F</option>
		            					</select>
		            				</div>
		            				<div class="form-group">
		            					<label>{{ $language_resource['language'] }}</label>
		            					<select class="form-control input" name="language">
											@if ( $user->language == 'ge')
												<option value="ge">ქართული</option>
												<option value="en">English</option>
											@else
												<option value="en">English</option>
		            							<option value="ge">ქართული</option>
											@endif
		            					</select>
		            				</div>
		            				<div class="form-group">
		            					<button class="btn btn-primary submit" name="submit">{{ $language_resource['save'] }}</button>
		            				</div>
		            			</form>
		            		</div>
	            		</div>
	            		<div class="col-md-6">
            				<div class="col-md-12 padding">

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
    
@endsection