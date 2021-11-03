
<div class="container-fluid main_container">
  <nav class="navbar navbar-default">
    <div class="container-fluid">
      <!-- Brand and toggle get grouped for better mobile display -->
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="{{ url('/') }}"><img src="{{ asset('/images/logo.png') }}" class="logo"></a>
      </div>

      <!-- Collect the nav links, forms, and other content for toggling -->
      <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
        <ul class="nav navbar-nav">
          <li class="{{ isset($active_menu_item) && $active_menu_item == 'monitoring' ? 'active' : NULL }}"><a href="{{ url('/') }}">{{ $language_resource['monitoring'] }}</a></li>
          <li class="{{ isset($active_menu_item) && $active_menu_item == 'reports' ? 'active' : NULL }}"><a href="{{ url('/reports') }}">{{ $language_resource['reports'] }}</a></li>
          <li class="{{ isset($active_menu_item) && $active_menu_item == 'administration' ? 'active' : NULL }}"><a href="{{ url('/administration') }}">{{ $language_resource['administration'] }}</a></li>    
        </ul>
      
        <ul class="nav navbar-nav navbar-right">

          <li class="alert_nav"><a href="#"><i class="fas fa-bell"></i></a></li>
          <li class="{{ isset($active_menu_item) && $active_menu_item == 'settings' ? 'active' : NULL }}"><a href="{{ url('/settings') }}"><i class="fas fa-user"></i></a></li>
          <li><a href="{{ url('logout') }}"><i class="fas fa-sign-out-alt"></i> {{ $language_resource['logout'] }}</a></li>

        </ul>
      </div><!-- /.navbar-collapse -->
    </div><!-- /.container-fluid -->
  </nav>

