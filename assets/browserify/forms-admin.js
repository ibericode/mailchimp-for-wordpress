'use strict';

// deps
let i18n = window.mc4wp_forms_i18n;
let global = window.mc4wp_vars;

const m = require('mithril');
let events = mc4wp.events;
let settings = mc4wp.settings;
let helpers = mc4wp.helpers;

let tabs = mc4wp.tabs;
const FormWatcher = require('./admin/form-editor/form-watcher.js');
const FormEditor = require('./admin/form-editor/form-editor.js');
const FieldHelper = require('./admin/form-editor/field-helper.js');
const FieldManager = require('./admin/form-editor/field-manager.js');
let fields = require('./admin/form-editor/fields.js')(m, events);

// vars
let editor = window.formEditor = FormEditor;
let watcher = new FormWatcher( m, formEditor, settings, fields, events, helpers );
let fieldHelper = new FieldHelper( m, tabs, formEditor, fields, events, i18n );
let fieldManager = new FieldManager(fields, i18n, settings, events, global.mailchimp.lists);
let notices = require('./admin/notices');

// mount field helper on element
let fieldHelperRootElement = document.getElementById( 'mc4wp-field-wizard');
m.mount(fieldHelperRootElement, fieldHelper);

// init notices
notices.init(editor, fields, settings);

// expose some methods
window.mc4wp = window.mc4wp || {};
window.mc4wp.forms = window.mc4wp.forms || {};
window.mc4wp.forms.editor = editor;
window.mc4wp.forms.fields = fields;
