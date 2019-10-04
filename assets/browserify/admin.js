'use strict';

// dependencies
import tlite from 'tlite';
const m = require('mithril');
const tabs = require ('./admin/tabs.js');
const events = require('./admin/events.js');
const settings = require('./admin/settings.js');
const helpers = require('./admin/helpers.js');

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
