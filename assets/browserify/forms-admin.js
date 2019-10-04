'use strict';

// deps
let i18n = window.mc4wp_forms_i18n;
let global = window.mc4wp_vars;
let mailchimpLists = global.mailchimp.lists;

const m = require('mithril');
let events = mc4wp.events;
let settings = mc4wp.settings;

const tabs = mc4wp.tabs;
const FormWatcher = require('./admin/form-editor/form-watcher.js');
const editor = require('./admin/form-editor/form-editor.js');
const FieldHelper = require('./admin/form-editor/field-helper.js');
const FieldManager = require('./admin/form-editor/field-manager.js');
let fields = require('./admin/form-editor/fields.js')(events);

// vars
let watcher = new FormWatcher(settings, fields, events);
let fieldManager = new FieldManager({fields, i18n, settings, events, mailchimpLists});
let notices = require('./admin/notices');

// mount field helper on element
let fieldHelper = new FieldHelper(tabs, fields, events, i18n);
let fieldHelperRootElement = document.getElementById( 'mc4wp-field-wizard');
m.mount(fieldHelperRootElement, fieldHelper);

// init notices
notices.init(fields, settings);

// expose some methods
window.mc4wp.forms = window.mc4wp.forms || {};
window.mc4wp.forms.editor = editor;
window.mc4wp.forms.fields = fields;
