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

	// vars
	var context = document.getElementById('mc4wp-admin');
	var formContentTextarea = document.getElementById('mc4wp-form-content');
	var tabs = require ('./admin/tabs.js')(context);
	var helpers = require('./admin/helpers.js');
	var settings = require('./admin/settings.js')(context, helpers);
	var fields = require('./admin/fields.js')(m);

	if( formContentTextarea ) {

		// instantiate form editor
		var formEditor = window.formEditor = new FormEditor( formContentTextarea );

		// run field factory (registers fields from merge vars & interest groupings of selected lists)
		var fieldsFactory = new FieldsFactory(settings,fields);
		fieldsFactory.work(settings.getSelectedLists());

		// instantiate form watcher
		var formWatcher = new FormWatcher( formEditor, settings, fields );

		// instantiate form field helper
		var fieldHelper = new FieldHelper( m, tabs, formEditor, fields );
		m.mount( document.getElementById( 'mc4wp-field-wizard'), fieldHelper );
	}

	// Lucy!
	var lucy = new Lucy(
		'https://mc4wp.com/',
		'DA9YFSTRKA',
		'ce1c93fad15be2b70e0aa0b1c2e52d8e',
		'wpkb_articles',
		[
			{
				text: "Knowledge Base",
				href: "https://mc4wp.com/kb/"
			},
			{
				text: "Code Reference",
				href: "http://developer.mc4wp.com/"
			},
			{
				text: "Changelog",
				href: "http://mc4wp.com/documentation/changelog/"
			}

		],
		'mailto:support@mc4wp.com'
	);

	// expose some things
	window.mc4wp = {
		helpers: helpers
	};
	window.m = m;
	window.mc4wp_register_field = fields.register;
	window.mc4wp_deregister_field = fields.deregister;
})();