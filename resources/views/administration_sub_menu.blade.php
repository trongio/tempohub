<div class="col-md-12 reports_sub_menu">
	<ul>
		<li class="{{ isset($active_sub_menu_item) && $active_sub_menu_item == 'warehouses' ? 'active' : NULL }}">
			<a href="/administration/">{{ $language_resource['warehouses'] }}</a>
		</li>
		<li class="{{ isset($active_sub_menu_item) && $active_sub_menu_item == 'sensors' ? 'active' : NULL }}">
			<a href="/administration/sensors">{{ $language_resource['sensors'] }}</a>
		</li>
		<li class="{{ isset($active_sub_menu_item) && $active_sub_menu_item == 'alerts' ? 'active' : NULL }}">
			<a href="/administration/alerts">{{ $language_resource['alerts'] }}</a>
		</li>
		<li class="{{ isset($active_sub_menu_item) && $active_sub_menu_item == 'sensors' ? 'active' : NULL }}">
			{{ session('langauge') }}
		</li>

	</ul>
</div>