'use strict';

var mc4wp = window.mc4wp || {};

// deps & vars
var Gator = require('gator');
var forms = require('./forms/forms.js');
var listeners = window.mc4wp && window.mc4wp.listeners ? window.mc4wp.listeners : [];
var config = window.mc4wp_forms_config || {};

// funcs
function handleFormSubmission(formConfig){
	var form,
		element = document.getElementById(formConfig.element_id);

	// get form by element, this might be a dummy element
	form = forms.getByElement(element);

	// add class & trigger event
	forms.trigger( 'submitted', [form]);

	// trigger eve
	if( formConfig.errors ) {
		// form has errors, repopulate it.
		form.setData(formConfig.data);
		forms.trigger('error', [form, formConfig.errors]);
	} else {
		// form was successfully submitted
		forms.trigger('success', [form, formConfig.data]);
		forms.trigger(formConfig.action + "d", [form, formConfig.data]);
	}

	if( element && config.auto_scroll ) {
		form.placeIntoView( (config.auto_scroll === 'animated') );
	}
}

// register early listeners
for(var i=0; i<listeners.length;i++) {
	forms.on(listeners[i].event, listeners[i].callback);
}


// was a form submitted?
if( config.submitted_form && config.submitted_form.id ) {
	handleFormSubmission(config.submitted_form);
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