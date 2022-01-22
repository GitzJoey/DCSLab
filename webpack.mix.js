const mix = require('laravel-mix');
const path = require('path');
const webpack = require('webpack');
const tailwindcss = require('tailwindcss');

mix.disableNotifications();

if (mix.inProduction()) {
    mix
        .sass('resources/sass/start/main.scss', 'public/css/start/main.css')
        .js('resources/js/start/main.js', 'public/js/start/main.js')
        .version()
    ;

    mix
        .webpackConfig({
            resolve: {
                alias: {
                    "@": path.resolve(__dirname, "resources/js/midone")
                }
            },
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
        .js('resources/js/midone/app.js','public/js/midone/main.js')
        .vue()
        .version()
    ;
} else {
    mix
        .webpackConfig({
            devtool: 'source-map'
        })
        .sass('resources/sass/start/main.scss', 'public/css/start/main.css')
        .js('resources/js/start/main.js', 'public/js/start/main.js')
        .sourceMaps()
    ;

    mix
        .webpackConfig({
            devtool: 'source-map',
            resolve: {
                alias: {
                    "@": path.resolve(__dirname, "resources/js/midone/")
                }
            },
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
        .js('resources/js/midone/app.js','public/js/midone/main.js')
        .sourceMaps()
        .vue()
    ;
}

