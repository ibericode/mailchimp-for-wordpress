(function() {
	'use strict';

	window.mc4wp = window.mc4wp || {};

	// dependencies
	var FormWatcher = require('./admin/form-watcher.js');
	var FormEditor = require('./admin/form-editor.js');
	var FieldHelper = require('./admin/field-helper.js');
	var FieldsFactory = require('./admin/fields-factory.js');
	var m = require('../third-party/mithril.js');
	var Lucy = require('./admin/lucy.js');
	var EventEmitter = require('../third-party/event-emitter.js');

	// vars
	var context = document.getElementById('mc4wp-admin')
	var events = new EventEmitter();;
	var formContentTextarea = document.getElementById('mc4wp-form-content');
	var tabs = require ('./admin/tabs.js')(context);
	var helpers = require('./admin/helpers.js');
	window.mc4wp.helpers = helpers;
	var settings = require('./admin/settings.js')(context, helpers, events);
	var fields = require('./admin/fields.js')(m, events);

	// are we on edit forms page?
	if( formContentTextarea ) {

		var formEditor = window.formEditor = new FormEditor( formContentTextarea );
		var formWatcher = new FormWatcher( formEditor, settings, fields, events );
		var fieldHelper = new FieldHelper( m, tabs, formEditor, fields );

		m.mount( document.getElementById( 'mc4wp-field-wizard'), fieldHelper );

		// register fields and redraw screen in 2.5 seconds
		var fieldsFactory = new FieldsFactory(settings,fields);
		events.on('selectedLists.change', fieldsFactory.work);
		fieldsFactory.work(settings.getSelectedLists());
		window.setTimeout( function() {m.redraw();}, 2500 );
	}

	// Lucy!
	var lucy = new Lucy(
		'https://mc4wp.com/',
		'DA9YFSTRKA',
		'ce1c93fad15be2b70e0aa0b1c2e52d8e',
		'wpkb_articles',
		[
			{
				text: "<span class=\"dashicons dashicons-book\"></span> Knowledge Base",
				href: "https://mc4wp.com/kb/"
			},
			{
				text: "<span class=\"dashicons dashicons-editor-code\"></span> Code Reference",
				href: "http://developer.mc4wp.com/"
			},
			{
				text: "<span class=\"dashicons dashicons-editor-break\"></span> Changelog",
				href: "http://mc4wp.com/documentation/changelog/"
			}

		],
		'mailto:support@mc4wp.com'
	);

	// expose some things
	// @TODO clean-up
	window.m = m;
	window.mc4wp_register_field = fields.register;
	window.mc4wp_deregister_field = fields.deregister;
})();