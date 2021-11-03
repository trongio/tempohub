const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
	.js('resources/js/sidebar_alerts.js', 'public/js')
	.js('resources/js/alerts.js', 'public/js')
	.js('resources/js/monitoring.js', 'public/js')
	.js('resources/js/reports.js', 'public/js')
	.js('resources/js/administration_warehouses.js', 'public/js')
	.js('resources/js/administration_warehouse_rooms.js', 'public/js')
	.js('resources/js/administration_warehouse_room_sensors.js', 'public/js')
	.js('resources/js/administration_warehouse_room_sensors_alerts.js', 'public/js')
	.js('resources/js/app.js', 'public/js')


    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/login.scss', 'public/css')
    .sass('resources/sass/main.scss', 'public/css')

    .copy('node_modules/@fortawesome/fontawesome-free/webfonts', 'public/webfonts')