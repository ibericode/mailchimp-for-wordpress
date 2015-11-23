'use strict';

var mc4wp = window.mc4wp || {};

// deps
var Gator = require('gator');
var forms = require('./forms/forms.js');
var listeners = window.mc4wp && window.mc4wp.listeners ? window.mc4wp.listeners : [];
var config = window.mc4wp_forms_config || {};

// register early listeners
for(var i=0; i<listeners.length;i++) {
	forms.on(listeners[i].event, listeners[i].callback);
}

// was a form submitted?
// TODO: take submitted element into account here.
if( config.submitted_form && config.submitted_form.id ) {
	var form = forms.get(config.submitted_form.id);

	// add class & trigger event
	forms.trigger( 'submitted', [form]);

	if( config.submitted_form.errors ) {
		// form has errors, repopulate it.
		form.setData(config.submitted_form.data);
		forms.trigger('error', [form, config.submitted_form.errors]);
	} else {
		// form was successfully submitted
		forms.trigger('success', [form, config.submitted_form.data]);
		forms.trigger(config.submitted_form.action + "d", [form, config.submitted_form.data]);
	}

	//if( config.auto_scroll ) {
	//	form.placeIntoView( config.auto_scroll === 'animated' );
	//}
}

// Bind browser events to form events (using delegation to work with AJAX loaded forms as well)
Gator(document.body).on('submit', '.mc4wp-form', function(event) {
	event = event || window.event;
	var form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('submit', [form, event]);
});

Gator(document.body).on('focus', '.mc4wp-form', function(event) {
	event = event || window.event;
	var form = forms.getByElement(event.target || event.srcElement);
	if( ! form.started ) {
		forms.trigger('start', [form, event]);
	}
});

Gator(document.body).on('change', '.mc4wp-form', function(event) {
	event = event || window.event;
	var form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('change', [form,event]);
});


// expose forms object
mc4wp.forms = forms;
window.mc4wp = mc4wp;