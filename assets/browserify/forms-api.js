'use strict';

var mc4wp = window.mc4wp || {};

// bail early if we're on IE8..
// TODO: just don't load in IE8
if( ! window.addEventListener ) {
	return;
}

// deps & vars
var Gator = require('gator');
var forms = require('./forms/forms.js');
var listeners = window.mc4wp && window.mc4wp.listeners ? window.mc4wp.listeners : [];
var config = window.mc4wp_forms_config || {};

// funcs
function triggerFormEvents(form,action,errors,data) {
	// trigger events
	forms.trigger( 'submitted', [form]);

	if( errors ) {
		forms.trigger('error', [form, errors]);
	} else {
		// form was successfully submitted
		forms.trigger('success', [form, data]);
		forms.trigger(action + "d", [form, data]);
	}
}

function handleFormRequest(form,action,errors,data){

	// get form by element, element might be null
	var animate;

	if( errors ) {
		form.setData(data);
	}

	if( scroll ) {
		animate = (scroll === 'animated');
		form.placeIntoView(animate);
	}

	// trigger events on window.load so all other scripts have loaded
	window.addEventListener('load', function(){
		triggerFormEvents(form, action, errors, data);
	})
}

// register early listeners
for(var i=0; i<listeners.length;i++) {
	forms.on(listeners[i].event, listeners[i].callback);
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

if( config.submitted_form ) {
	var formConfig = config.submitted_form,
		element = document.getElementById(formConfig.element_id),
		form = forms.getByElement(element);

	handleFormRequest(form,formConfig.action, formConfig.errors,formConfig.data);
}

// expose forms object
mc4wp.forms = forms;
window.mc4wp = mc4wp;