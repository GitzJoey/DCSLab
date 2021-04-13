import jQuery from 'jquery';
import SimpleBar from 'simplebar';
import Cookies from 'js-cookie';
import 'bootstrap';
import 'popper.js';
import 'jquery.appear';
import 'jquery-scroll-lock';
import 'jquery-countto';

window.$ = window.jQuery    = jQuery;
window.SimpleBar            = SimpleBar;
window.Cookies              = Cookies;

window._ = require('lodash');

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common["Accept"] = "application/json"

let token = document.head.querySelector('meta[name="csrf-token"]');
let language = document.documentElement.lang;

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

if (language) {
    window.axios.defaults.headers.common['X-localization'] = language;
} else {
    console.error('X-localization not found.');
}