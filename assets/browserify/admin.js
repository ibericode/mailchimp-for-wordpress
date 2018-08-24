'use strict';

// dependencies
var m = window.m = require('mithril');
var EventEmitter = require('wolfy87-eventemitter');

// vars
var context = document.getElementById('pl4wp-admin');
var events = new EventEmitter();
var tabs = require ('./admin/tabs.js')(context);
var helpers = require('./admin/helpers.js');
var settings = require('./admin/settings.js')(context, helpers, events);

import tlite from 'tlite';
tlite(el => el.className.indexOf('pl4wp-tooltip') > -1 );

// list fetcher
var ListFetcher = require('./admin/list-fetcher.js');
var mount = document.getElementById('pl4wp-list-fetcher');
if( mount ) {
    m.mount(mount, new ListFetcher);
}

// expose some things
window.pl4wp = window.pl4wp || {};
window.pl4wp.deps = window.pl4wp.deps || {};
window.pl4wp.deps.mithril = m;
window.pl4wp.helpers = helpers;
window.pl4wp.events = events;
window.pl4wp.settings = settings;
window.pl4wp.tabs = tabs;
