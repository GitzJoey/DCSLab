const mix = require('laravel-mix');

mix.disableNotifications();

mix.sass('resources/sass/start/main.scss', 'public/css/start/main.css')
    .js('resources/js/start/app.js', 'public/js/start/app.js')
;
