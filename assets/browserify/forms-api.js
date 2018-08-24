'use strict';

// deps & vars
var pl4wp = window.pl4wp || {};
var Gator = require('gator');
var forms = require('./forms/forms.js');
var config = window.pl4wp_forms_config || {};
var scrollToElement = require('scroll-to-element');
import ConditionalElements from './forms/conditional-elements.js';

// funcs
function scrollToForm(form) {
	var animate = config.auto_scroll === 'animated';

	scrollToElement(form.element, { 
		duration: animate ? 800 : 1, 
		alignment: 'middle'
	});
}

function handleFormRequest(form, action, errors, data){
	var timeStart = Date.now();
	var pageHeight = document.body.clientHeight;

	// re-populate form
	if( errors ) {
		form.setData(data);
	}

	// scroll to form
	if( window.scrollY <= 10 && config.auto_scroll ) {
		scrollToForm(form);
	}

	// trigger events on window.load so all other scripts have loaded
	window.addEventListener('load', function() {
		// trigger events
		forms.trigger('submitted', [form]);
		forms.trigger(form.id + '.submitted', [form]);

		if( errors ) {
			forms.trigger('error', [form, errors]);
			forms.trigger(form.id + '.error', [form, errors]);
		} else {
			// form was successfully submitted
			forms.trigger('success', [form, data]);
			forms.trigger(form.id + '.success', [form, data]);

			// subscribed / unsubscribed
			forms.trigger(action + "d", [form, data]);
			forms.trigger(form.id + "." + action + "d", [form, data]);
		}

		// scroll to form again if page height changed since last scroll, eg because of slow loading images
		// (only if load didn't take more than 0.8 seconds to prevent overtaking user scroll)
		var timeElapsed = Date.now() - timeStart;
 		if( config.auto_scroll && timeElapsed > 1000 && timeElapsed < 2000 && document.body.clientHeight != pageHeight ) {
 			scrollToForm(form);
 		}
	});
}

// Bind browser events to form events (using delegation)
Gator(document.body).on('submit', '.pl4wp-form', function(event) {
	var form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('submit', [form, event]);
	forms.trigger(form.id + '.submit', [ form, event]);
});

Gator(document.body).on('focus', '.pl4wp-form', function(event) {
	var form = forms.getByElement(event.target || event.srcElement);

	if( ! form.started ) {
		forms.trigger('started', [form, event]);
		forms.trigger(form.id + '.started', [form, event]);
		form.started = true;
	}
});

Gator(document.body).on('change', '.pl4wp-form', function(event) {
	var form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('change', [form,event]);
	forms.trigger(form.id + '.change', [form,event]);
});

// init conditional elements
ConditionalElements.init();

// register early listeners
if( pl4wp.listeners ) {
    var listeners = pl4wp.listeners;
    for(var i=0; i<listeners.length;i++) {
        forms.on(listeners[i].event, listeners[i].callback);
    }

    // delete temp listeners array, so we don't bind twice
    delete pl4wp["listeners"];
}

// expose forms object
pl4wp.forms = forms;

// handle submitted form
if( config.submitted_form ) {
	var formConfig = config.submitted_form,
		element = document.getElementById(formConfig.element_id),
		form = forms.getByElement(element);

	handleFormRequest(form, formConfig.action, formConfig.errors, formConfig.data);
}

// expose pl4wp object globally
window.pl4wp = pl4wp;

