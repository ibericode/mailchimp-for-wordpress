'use strict';

function init() {
	// deps
	var Gator = require('../third-party/gator.js');
	var forms = require('./forms/forms.js');
	var listeners = window.mc4wpFormListeners || [];
	var config = window.mc4wp_forms_config || {};

	// register early listeners
	if( listeners ) {
		listeners.forEach(function(listener) {
			forms.on(listener.event, listener.callback);
		});
	}

	// was a form submitted?
	if( config.submitted_form ) {
		var form = forms.get(config.submitted_form.id);

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
	}

// Bind browser events to form events (using delegation to work with AJAX loaded forms as well)
	Gator(document.body).on('submit', '.mc4wp-form', function(event) {
		var form = forms.getByElement(event.target);
		forms.trigger('submit', [form, event]);
	});

	Gator(document.body).on('focus', '.mc4wp-form', function(event) {
		var form = forms.getByElement(event.target);
		if( ! form.started ) {
			forms.trigger('start', [form, event]);
		}
	});

	Gator(document.body).on('change', '.mc4wp-form', function(event) {
		var form = forms.getByElement(event.target);
		forms.trigger('change', [form,event]);
	});

	// expose stuff, this overrides dummy javascript
	window.mc4wp = {};
	window.mc4wp.forms = forms;
	delete window.mc4wpFormListeners;
}

// if missing features, load shim and try again
if( ! Array.prototype.forEach ) {
	var shim = document.createElement('script');
	shim.setAttribute('src','https://cdnjs.cloudflare.com/ajax/libs/es5-shim/4.3.1/es5-shim.min.js');
	document.head.appendChild(shim);
	shim.onload = init;
} else {
	init();
}
