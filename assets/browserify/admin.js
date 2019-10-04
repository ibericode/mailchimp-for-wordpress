'use strict';

// dependencies
import tlite from 'tlite';
const m = require('mithril');
const EventEmitter = require('wolfy87-eventemitter');
const Tabs = require ('./admin/tabs.js');
const Settings = require('./admin/settings.js');
const helpers = require('./admin/helpers.js');

// vars
const context = document.getElementById('mc4wp-admin');
let tabs, settings;
const events = new EventEmitter();

if (context !== null) {
    tabs = Tabs(context);
    settings = Settings(context, helpers, events);
}

tlite(el => el.className.indexOf('mc4wp-tooltip') > -1 );

// list fetcher
const ListFetcher = require('./admin/list-fetcher.js');
const mount = document.getElementById('mc4wp-list-fetcher');
if( mount ) {
    m.mount(mount, ListFetcher);
}

require('./admin/fields/mailchimp-api-key.js');
require('./admin/list-overview.js');

// expose some things
window.mc4wp = window.mc4wp || {};
window.mc4wp.helpers = helpers;
window.mc4wp.events = events;
window.mc4wp.settings = settings;
window.mc4wp.tabs = tabs;
