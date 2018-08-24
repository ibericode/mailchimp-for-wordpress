'use strict';

// deps
var i18n = window.pl4wp_forms_i18n;
var m = window.pl4wp.deps.mithril;
var events = pl4wp.events;
var settings = pl4wp.settings;
var helpers = pl4wp.helpers;

var tabs = pl4wp.tabs;
var FormWatcher = require('./admin/form-watcher.js');
var FormEditor = require('./admin/form-editor.js');
var FieldHelper = require('./admin/field-helper.js');
var FieldsFactory = require('./admin/fields-factory.js');
var fields = require('./admin/fields.js')(m, events);

// vars
var editor = window.formEditor = FormEditor;
var watcher = new FormWatcher( m, formEditor, settings, fields, events, helpers );
var fieldHelper = new FieldHelper( m, tabs, formEditor, fields, events, i18n );
var notices = require('./admin/notices');

// mount field helper on element
m.mount( document.getElementById( 'pl4wp-field-wizard'), fieldHelper );

// register fields and redraw screen in 2 seconds (fixes IE8 bug)
var fieldsFactory = new FieldsFactory(fields, i18n);
events.on('selectedLists.change', fieldsFactory.registerListsFields);
fieldsFactory.registerListsFields(settings.getSelectedLists());
fieldsFactory.registerCustomFields(pl4wp_vars.phplist.lists);

window.setTimeout( function() { m.redraw();}, 2000 );

// init notices
notices.init(editor, fields);

// expose some methods
window.pl4wp = window.pl4wp || {};
window.pl4wp.forms = window.pl4wp.forms || {};
window.pl4wp.forms.editor = editor;
window.pl4wp.forms.fields = fields;
