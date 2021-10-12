const mix = require('laravel-mix');

mix.disableNotifications();

mix
    .sass('resources/sass/start/main.scss', 'public/css/start/main.css')
    .js('resources/js/start/main.js', 'public/js/start/main.js')
    .version()
;
