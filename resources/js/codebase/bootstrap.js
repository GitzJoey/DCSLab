/*
 *  Document   : bootstrap.js
 *  Author     : pixelcave
 *  Description: Import global dependencies
 *
 */

/*
 ********************************************************************************************
 *
 * If you would like to use webpack to handle all required core JS files, you can uncomment
 * the following imports and window assignments to have them included in the compiled
 * codebase.app.min.js as well.
 *
 * After that change, you won't have to include codebase.core.min.js in your pages any more
 *
 *********************************************************************************************
 */

// Import all vital core JS files..
import jQuery from 'jquery';
import SimpleBar from 'simplebar';
import Cookies from 'js-cookie';
import 'bootstrap';
import 'popper.js';
import 'jquery.appear';
import 'jquery-scroll-lock';
import 'jquery-countto';

// ..and assign to window the ones that need it
window.$ = window.jQuery    = jQuery;
window.SimpleBar            = SimpleBar;
window.Cookies              = Cookies;

window._ = require('lodash');

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
let language = document.documentElement.lang;

if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

window.Vue = require('vue')
