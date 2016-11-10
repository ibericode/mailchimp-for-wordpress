'use strict';

// deps
var i18n = window.mc4wp_forms_i18n;
var m = window.mc4wp.deps.mithril;
var events = mc4wp.events;
var settings = mc4wp.settings;
var helpers = mc4wp.helpers;

var tabs = mc4wp.tabs;
var FormWatcher = require('./admin/form-watcher.js');
var FormEditor = require('./admin/form-editor.js');
var FieldHelper = require('./admin/field-helper.js');
var FieldsFactory = require('./admin/fields-factory.js');
var fields = require('./admin/fields.js')(m, events);

// vars
var textareaElement = document.getElementById('mc4wp-form-content');
var editor = window.formEditor = new FormEditor( textareaElement );
var watcher = new FormWatcher( m, formEditor, settings, fields, events, helpers );
var fieldHelper = new FieldHelper( m, tabs, formEditor, fields, events, i18n );
var notices = require('./admin/notices');

// mount field helper on element
m.mount( document.getElementById( 'mc4wp-field-wizard'), fieldHelper );

// register fields and redraw screen in 2 seconds (fixes IE8 bug)
var fieldsFactory = new FieldsFactory(fields, i18n);
events.on('selectedLists.change', fieldsFactory.registerListsFields);
fieldsFactory.registerListsFields(settings.getSelectedLists());
fieldsFactory.registerCustomFields(mc4wp_vars.mailchimp.lists);

window.setTimeout( function() { m.redraw();}, 2000 );

// init notices
notices.init(editor);

// expose some methods
window.mc4wp = window.mc4wp || {};
window.mc4wp.forms = window.mc4wp.forms || {};
window.mc4wp.forms.editor = editor;
window.mc4wp.forms.fields = fields;
