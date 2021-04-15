window.Popper = require('popper.js').default;

try {
    window.$ = window.jQuery = require('jquery');

    require('bootstrap');
    require('jquery-easing');
    require('magnific-popup');
} catch (e) {
    console.log(e.message);
}
