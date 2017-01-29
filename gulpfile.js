var elixir = require('laravel-elixir');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix.styles('bootstrap.css')
    mix.sass(['./public/css/bootstrap.css','inspinia/style.scss'], 'public/css/all.css');
    mix.scripts(['jquery-2.1.1.js',
    	'bootstrap.min.js',
    	'plugins/metisMenu/jquery.metisMenu.js',
    	'plugins/slimscroll/jquery.slimscroll.min.js',
    	'inspinia.js',
    	'plugins/pace/pace.min.js'])
});
