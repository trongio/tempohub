@extends('layouts.app')

@section('content')

<div class="container-fluid main_container">
    <div class="row">
        <div class="col-md-7 left_container">
        </div>
        <div class="col-sm-12 col-md-5 right_container">
            <div class="col-md-12 login_wrapper">
                <div class="col-md-12 login_logo_wrapper">
                   
                </div>
                <div class="col-md-12 login_form_wrapper">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1"><i class='fas fa-user'></i></span>
                            <input id="nickname" type="text" class="form-control input @error('nickname') is-invalid @enderror" name="nickname" value="{{ old('nickname') }}" required autocomplete="nickname" autofocus placeholder="სახელი" aria-describedby="basic-addon1">
                
                             @error('nickname')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="input-group">

                            <span class="input-group-text" id="basic-addon2"><i class="fas fa-lock"></i></span>

                            <input id="password" type="password" class="form-control input @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="პაროლი" aria-describedby="basic-addon2">

                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                                <label class="form-check-label" for="remember">
                                    პაროლის დამახსოვრება
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary login_button">
                                შესვლა
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection