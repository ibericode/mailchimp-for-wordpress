(function() {
	'use strict';

	// dependencies
	var Tabs = require('./Tabs.js');
	var FormWatcher = require('./FormWatcher.js');
	var FormEditor = require('./FormEditor.js');
	var FieldHelper = require('./FieldHelper.js');
	var Settings = require('./Settings.js');


	// vars
	var context = document.getElementById('mc4wp-admin');
	var form_content_textarea = document.getElementById('mc4wp-form-content');
	var form_editor = window.form_editor = new FormEditor( form_content_textarea );
	var settings = new Settings(context);
	var tabs = new Tabs(context, form_editor );
	var form_watcher = new FormWatcher( form_editor, settings );
	var field_helper = new FieldHelper( settings, tabs, form_editor );
	m.mount( document.getElementById( 'mc4wp-field-wizard'), field_helper );

	// @todo: clean this up
	require('./clean-this-up.js');
})();