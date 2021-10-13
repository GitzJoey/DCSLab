const mix = require('laravel-mix');
const webpack = require('webpack');
const tailwindcss = require('tailwindcss');

mix.disableNotifications();

mix
    .sass('resources/sass/start/main.scss', 'public/css/start/main.css')
    .js('resources/js/start/main.js', 'public/js/start/main.js')
    .version()
;

mix
    .webpackConfig({
        plugins: [
            new webpack.DefinePlugin({
                __VUE_OPTIONS_API__: true,
                __VUE_PROD_DEVTOOLS__: false
            }),
            new webpack.ProvidePlugin({
                cash: 'cash-dom',
                Popper: '@popperjs/core'
            })
        ]
    })
    .sass('resources/sass/midone/app.scss', 'public/css/midone/app.css')
    .options({
        postCss: [
            require('autoprefixer'),
            tailwindcss('./tailwind.config.js'),
        ]
    })
    .copy('resources/css/midone/', 'public/css/midone/')
    .js('resources/js/midone/app.js','public/js/midone/main.js')
    .vue()
    .version()
;
