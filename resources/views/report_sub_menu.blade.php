<div class="col-md-12 reports_sub_menu">
	<ul>
		<li class="{{ isset($active_sub_menu_item) && $active_sub_menu_item == 'general' ? 'active' : NULL }}">
			<a href="/reports">{{ $language_resource['general'] }}</a>
		</li>
		<li class="{{ isset($active_sub_menu_item) && $active_sub_menu_item == 'alerts' ? 'active' : NULL }}">
			<a href="/reports/alerts">{{ $language_resource['alerts'] }}</a>
		</li>
		<?php
		// <li class="{{ isset($active_sub_menu_item) && $active_sub_menu_item == 'user_actions' ? 'active' : NULL }}">
		// 	<a href="/reports/user-actions">{{ $language_resource['activity-log'] }}</a>
		// </li>
		?>
	</ul>
</div>