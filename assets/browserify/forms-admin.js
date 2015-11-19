'use strict';

// deps
var events = mc4wp.events;
var settings = mc4wp.settings;
var tabs = mc4wp.tabs;
var FormWatcher = require('./admin/form-watcher.js');
var FormEditor = require('./admin/form-editor.js');
var FieldHelper = require('./admin/field-helper.js');
var FieldsFactory = require('./admin/fields-factory.js');
var fields = require('./admin/fields.js')(m, events);

// vars
var textareaElement = document.getElementById('mc4wp-form-content');
var editor = window.formEditor = new FormEditor( textareaElement );
var watcher = new FormWatcher( formEditor, settings, fields, events );
var fieldHelper = new FieldHelper( m, tabs, formEditor, fields );

// mount field helper on element
m.mount( document.getElementById( 'mc4wp-field-wizard'), fieldHelper );

// register fields and redraw screen in 2 seconds (fixes IE8 bug)
var fieldsFactory = new FieldsFactory(settings,fields);
events.on('selectedLists.change', fieldsFactory.work);
fieldsFactory.work(settings.getSelectedLists());
window.setTimeout( function() {m.redraw();}, 2000 );

// expose some methods
window.mc4wp = window.mc4wp || {};
window.mc4wp.forms = window.mc4wp.forms || {};
window.mc4wp.forms.editor = editor;
window.mc4wp.forms.fields = fields;
