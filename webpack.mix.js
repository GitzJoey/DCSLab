const mix = require('laravel-mix');
const webpack = require('webpack')

mix.disableNotifications();

mix
    .sass('resources/sass/start/main.scss', 'public/css/start/main.css')
    .js('resources/js/start/app.js', 'public/js/start/app.js')
    .version()
;

mix
    .sass('resources/sass/codebase/main.scss', 'public/css/codebase/codebase.css')
    .sass('resources/sass/codebase/codebase/themes/corporate.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/earth.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/elegance.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/flat.scss', 'public/css/codebase/themes/')
    .sass('resources/sass/codebase/codebase/themes/pulse.scss', 'public/css/codebase/themes/')
    .js('resources/js/codebase/app.js', 'public/js/codebase/codebase.app.js')
    .version()
;

mix
    .webpackConfig({
        plugins: [
            new webpack.DefinePlugin({
                __VUE_OPTIONS_API__: true,
                __VUE_PROD_DEVTOOLS__: false
            })
        ]
    })
    .js('resources/js/apps/role.js','public/js/apps/role.js')
    .vue()
    .version()
;
