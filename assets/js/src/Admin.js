(function() {
	'use strict';

	window.mc4wp = window.mc4wp || {};

	// dependencies
	var FormWatcher = require('./FormWatcher.js');
	var FormEditor = require('./FormEditor.js');
	var FieldHelper = require('./FieldHelper.js');
	var FieldsFactory = require('./FieldsFactory.js');
	var m = require('../third-party/mithril.js');

	// vars
	var context = document.getElementById('mc4wp-admin');
	var formContentTextarea = document.getElementById('mc4wp-form-content');
	var tabs = require ('./Tabs.js')(context);
	var settings = require('./Settings.js')(context);
	var fields = require('./Fields.js')(m);

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

	// convenience methods
	window.m = m;
	window.mc4wp_register_field = fields.register;
	window.mc4wp_deregister_field = fields.deregister;
})();