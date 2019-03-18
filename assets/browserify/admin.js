'use strict';

// dependencies
import tlite from 'tlite';
var m = window.m = require('mithril');
var EventEmitter = require('wolfy87-eventemitter');
var Tabs = require ('./admin/tabs.js')
var Settings = require('./admin/settings.js')
var helpers = require('./admin/helpers.js');

// vars
var context = document.getElementById('mc4wp-admin');
var tabs, settings;
var events = new EventEmitter();

if (context !== null) {
    tabs = Tabs(context);
    settings = Settings(context, helpers, events);
}

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
