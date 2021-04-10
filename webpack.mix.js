const mix = require('laravel-mix');
const webpack = require('webpack')

mix.disableNotifications();

mix
    .sass('resources/sass/codebase/main.scss', 'public/css/codebase/codebase.css')
    .sass('resources/sass/codebase/codebase/themes/corporate.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/earth.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/elegance.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/flat.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/pulse.scss', 'public/css/codebase/themes/')
    .js('resources/js/codebase/app.js', 'public/js/codebase/codebase.app.js')
    .js('resources/js/apps/role.js', 'public/js/apps/role.js')
    .webpackConfig ({
        plugins: [
            new webpack.DefinePlugin({
                __VUE_OPTIONS_API__: false,
                __VUE_PROD_DEVTOOLS__: false,
            }),
        ],
    })
;

mix.copy('resources/fonts/Nunito_Sans/NunitoSans-Regular.ttf', 'public/fonts');
mix.copy('node_modules/simplebar/dist/simplebar.esm.js.map', 'public/js/codebase');

mix.copy('resources/css/start', 'public/css/start')
    .copy('resources/js/start', 'public/js/start')
    .copy('resources/fonts/LineIcons', 'public/fonts')
;
