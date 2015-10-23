(function() {
	'use strict';

	// dependencies
	var FormWatcher = require('./FormWatcher.js');
	var FormEditor = require('./FormEditor.js');
	var FieldHelper = require('./FieldHelper.js');

	// vars
	var form_editor = window.form_editor = new FormEditor( document.getElementById('mc4wp-form-content'));
	var form_watcher = new FormWatcher( form_editor );
	var field_helper = new FieldHelper();
	m.mount( document.getElementById( 'mc4wp-field-wizard'), field_helper );

	// events
	form_editor.on('change', form_watcher.checkRequiredFields );

	// @todo: clean this up
	require('./clean-this-up.js');

})();