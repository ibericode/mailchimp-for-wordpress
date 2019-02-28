'use strict';

// dependencies
var m = window.m = require('mithril');
var EventEmitter = require('wolfy87-eventemitter');

// vars
var context = document.getElementById('mc4wp-admin');
var events = new EventEmitter();
var tabs = require ('./admin/tabs.js')(context);
var helpers = require('./admin/helpers.js');
var settings = require('./admin/settings.js')(context, helpers, events);

import tlite from 'tlite';
tlite(el => el.className.indexOf('mc4wp-tooltip') > -1 );

// list fetcher
var ListFetcher = require('./admin/list-fetcher.js');
var mount = document.getElementById('mc4wp-list-fetcher');
if( mount ) {
    m.mount(mount, new ListFetcher);
}

require('./admin/fields/mailchimp-api-key.js');

// expose some things
window.mc4wp = window.mc4wp || {};
window.mc4wp.deps = window.mc4wp.deps || {};
window.mc4wp.deps.mithril = m;
window.mc4wp.helpers = helpers;
window.mc4wp.events = events;
window.mc4wp.settings = settings;
window.mc4wp.tabs = tabs;
