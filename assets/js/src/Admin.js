(function() {
	'use strict';

	window.mc4wp = window.mc4wp || {};

	// dependencies
	var Tabs = require('./Tabs.js');
	var FormWatcher = require('./FormWatcher.js');
	var FormEditor = require('./FormEditor.js');
	var FieldHelper = require('./FieldHelper.js');
	var Settings = require('./Settings.js');
	var fields = require('./Fields.js');
	var FieldsFactory = require('./FieldsFactory.js');

	// vars
	var context = document.getElementById('mc4wp-admin');
	var form_content_textarea = document.getElementById('mc4wp-form-content');
	var settings = new Settings(context);
	var tabs = new Tabs(context);

	if( form_content_textarea ) {
		window.m = require('../third-party/mithril.js');

		// instantiate form editor
		var form_editor = window.form_editor = new FormEditor( form_content_textarea );

		// run field factory (registers fields from merge vars & interest groupings of selected lists)
		var fields_factory = new FieldsFactory(settings,fields);
		fields_factory.work(settings.getSelectedLists());

		// instantiate form watcher
		var form_watcher = new FormWatcher( form_editor, settings, fields );

		// instantiate form field helper
		var field_helper = new FieldHelper( settings, tabs, form_editor, fields );
		m.mount( document.getElementById( 'mc4wp-field-wizard'), field_helper );
	}

	// expose some methods
	window.mc4wp_register_field = fields.register;
	window.mc4wp_deregister_field = fields.deregister;
})();