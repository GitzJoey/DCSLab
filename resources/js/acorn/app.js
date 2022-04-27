window._ = require('lodash');

require('bootstrap');

window.$ = require('cash-dom');

window.axios = require('axios');
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';