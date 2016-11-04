/* global $ */

require('bootstrap/dist/css/bootstrap.css');
require('bootstrap/dist/js/bootstrap.js');
require('font-awesome/scss/font-awesome.scss');
require('./fonts/roboto.scss');
require('./web/css/style.scss');

window.destiny = require('./web/js/destiny.js')['default'];
require('imports?this=>window!./web/js/ui.js');