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
    .js('resources/js/apps/inbox.js','public/js/apps/inbox.js')

    .js('resources/js/apps/company.js','public/js/apps/company.js')
    .js('resources/js/apps/branch.js','public/js/apps/branch.js')
    .js('resources/js/apps/warehouse.js','public/js/apps/warehouse.js')
    .js('resources/js/apps/cash.js','public/js/apps/cash.js')
    .js('resources/js/apps/supplier.js','public/js/apps/supplier.js')
    .js('resources/js/apps/productgroup.js','public/js/apps/productgroup.js')
    .js('resources/js/apps/productbrand.js','public/js/apps/productbrand.js')
    .js('resources/js/apps/unit.js','public/js/apps/unit.js')
    .js('resources/js/apps/product.js','public/js/apps/product.js')
    .js('resources/js/apps/customergroup.js','public/js/apps/customergroup.js')
    .js('resources/js/apps/customer.js','public/js/apps/customer.js')

    .vue()
    .version()
;

mix
	.copy('resources/css/doctoraccounting/animate.css', 'public/css/doctoraccounting')
	.copy('resources/css/doctoraccounting/bootstrap.min.css', 'public/css/doctoraccounting')
	.copy('resources/css/doctoraccounting/line-icons.css', 'public/css/doctoraccounting')
	.copy('resources/css/doctoraccounting/magnific-popup.css', 'public/css/doctoraccounting')
	.copy('resources/css/doctoraccounting/main.css', 'public/css/doctoraccounting')
	.copy('resources/css/doctoraccounting/nivo-lightbox.css', 'public/css/doctoraccounting')
	.copy('resources/css/doctoraccounting/owl.carousel.css', 'public/css/doctoraccounting')
	.copy('resources/css/doctoraccounting/owl.theme.css', 'public/css/doctoraccounting')
	.copy('resources/css/doctoraccounting/responsive.css', 'public/css/doctoraccounting')
	
	.copy('resources/fonts/doctoraccounting/LineIcons.eot', 'public/fonts/doctoraccounting')
	.copy('resources/fonts/doctoraccounting/LineIcons.svg', 'public/fonts/doctoraccounting')
	.copy('resources/fonts/doctoraccounting/LineIcons.ttf', 'public/fonts/doctoraccounting')
	.copy('resources/fonts/doctoraccounting/LineIcons.woff', 'public/fonts/doctoraccounting')
    
	.copy('resources/js/doctoraccounting/bootstrap.min.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/jquery.counterup.min.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/jquery.easing.min.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/jquery.magnific-popup.min.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/jquery.mixitup.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/jquery.nav.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/jquery.vide.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/jquery-min.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/main.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/nivo-lightbox.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/owl.carousel.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/popper.min.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/scrolling-nav.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/waypoints.min.js', 'public/js/doctoraccounting')
	.copy('resources/js/doctoraccounting/wow.js', 'public/js/doctoraccounting')
;