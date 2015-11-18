'use strict';

window.mc4wp = window.mc4wp || {};

// dependencies
var FormWatcher = require('./admin/form-watcher.js');
var FormEditor = require('./admin/form-editor.js');
var FieldHelper = require('./admin/field-helper.js');
var FieldsFactory = require('./admin/fields-factory.js');
var m = require('mithril');
var EventEmitter = require('wolfy87-eventemitter');

// vars
var context = document.getElementById('mc4wp-admin');
var events = new EventEmitter();
var formContentTextarea = document.getElementById('mc4wp-form-content');
var tabs = require ('./admin/tabs.js')(context);
var helpers = require('./admin/helpers.js');
var settings = require('./admin/settings.js')(context, helpers, events);
var fields = require('./admin/fields.js')(m, events);

// are we on edit forms page?
if( formContentTextarea ) {

	var formEditor = window.formEditor = new FormEditor( formContentTextarea );
	var formWatcher = new FormWatcher( formEditor, settings, fields, events );
	var fieldHelper = new FieldHelper( m, tabs, formEditor, fields );

	m.mount( document.getElementById( 'mc4wp-field-wizard'), fieldHelper );

	// register fields and redraw screen in 2 seconds (fixes IE8 bug)
	var fieldsFactory = new FieldsFactory(settings,fields);
	events.on('selectedLists.change', fieldsFactory.work);
	fieldsFactory.work(settings.getSelectedLists());
	window.setTimeout( function() {m.redraw();}, 2000 );
}



// expose some things
window.m = m;
window.mc4wp.deps = {
	mithril: m
};
window.mc4wp.form = {
	editor: formEditor,
	fields: fields
};
window.mc4wp.helpers = helpers;
window.mc4wp.events = events;
window.mc4wp.settings = settings;
window.mc4wp.tabs = tabs;
window.mc4wp_register_field = fields.register;
window.mc4wp_deregister_field = fields.deregister;
