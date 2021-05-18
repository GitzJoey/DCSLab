const mix = require('laravel-mix');
const webpack = require('webpack')
const tailwindcss = require("tailwindcss");

mix.disableNotifications();

mix
    .sass('resources/sass/start/main.scss', 'public/css/start/main.css')
    .js('resources/js/start/app.js', 'public/js/start/app.js')
    .sourceMaps()
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
    .copy('node_modules/simplebar/dist/simplebar.esm.js.map', 'public/js/codebase')
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
    .js('resources/js/apps/profile.js','public/js/apps/profile.js')
    .js('resources/js/apps/role.js','public/js/apps/role.js')
    .js('resources/js/apps/user.js','public/js/apps/user.js')
    .js('resources/js/apps/company.js','public/js/apps/company.js')
    .js('resources/js/apps/branch.js','public/js/apps/branch.js')
    .js('resources/js/apps/warehouse.js','public/js/apps/warehouse.js')
    .js('resources/js/apps/financecash.js','public/js/apps/financecash.js')
    .js('resources/js/apps/productgroup.js','public/js/apps/productgroup.js')
    .js('resources/js/apps/productbrand.js','public/js/apps/productbrand.js')
    .js('resources/js/apps/productunit.js','public/js/apps/productunit.js')
    .js('resources/js/apps/product.js','public/js/apps/product.js')
    .js('resources/js/apps/customergroup.js','public/js/apps/customergroup.js')
    .js('resources/js/apps/customer.js','public/js/apps/customer.js')







    .vue()
    .version()
;
