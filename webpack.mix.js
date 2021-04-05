const mix = require('laravel-mix');

mix.disableNotifications();

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .sourceMaps();

mix
    .sass('resources/sass/codebase/main.scss', 'public/css/codebase/codebase.css')
    .sass('resources/sass/codebase/codebase/themes/corporate.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/earth.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/elegance.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/flat.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/pulse.scss', 'public/css/codebase/themes/')
    .js('resources/js/codebase/app.js', 'public/js/codebase/codebase.app.js')
;

mix.copy('resources/fonts/Nunito_Sans/NunitoSans-Regular.ttf', 'public/fonts');
