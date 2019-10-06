'use strict';

// deps & vars
const mc4wp = window.mc4wp || {};
const Gator = require('gator');
const forms = require('./forms/forms.js');
const config = window.mc4wp_forms_config || {};
const scrollToElement = require('scroll-to-element');
import ConditionalElements from './forms/conditional-elements.js';

// funcs
function scrollToForm(form) {
	const animate = config.auto_scroll === 'animated';

	scrollToElement(form.element, {
		duration: animate ? 800 : 1,
		alignment: 'middle'
	});
}

function handleFormRequest(form, eventName, errors, data){
	const timeStart = Date.now();
	const pageHeight = document.body.clientHeight;

	// re-populate form if an error occurred
	if (errors) {
		form.setData(data);
	}

	// scroll to form
	if( window.scrollY <= 10 && config.auto_scroll ) {
		scrollToForm(form);
	}

	// trigger events on window.load so all other scripts have loaded
	window.addEventListener('load', function() {
		// trigger events
		forms.trigger(form.id + '.submitted', [form]);
		forms.trigger('submitted', [form]);

		if( errors ) {
			forms.trigger(form.id + '.error', [form, errors]);
			forms.trigger('error', [form, errors]);
		} else {
			// form was successfully submitted
			forms.trigger(form.id + '.success', [form, data]);
			forms.trigger('success', [form, data]);

			// subscribed / unsubscribed
			forms.trigger(form.id + "." + eventName, [form, data]);
			forms.trigger(eventName, [form, data]);

			// for BC: always trigger "subscribed" event when firing "updated_subscriber" event
			if( eventName === 'updated_subscriber' ) {
				forms.trigger(form.id + "." + "subscribed", [form, data, true]);
				forms.trigger('subscribed', [form, data, true]);
			}

		}

		// scroll to form again if page height changed since last scroll, eg because of slow loading images
		// (only if load didn't take more than 0.8 seconds to prevent overtaking user scroll)
		const timeElapsed = Date.now() - timeStart;
 		if( config.auto_scroll && timeElapsed > 1000 && timeElapsed < 2000 && document.body.clientHeight !== pageHeight ) {
 			scrollToForm(form);
 		}
	});
}

// Bind browser events to form events (using delegation)
let gator = Gator(document.body);
gator.on('submit', '.mc4wp-form', function(event) {
	const form = forms.getByElement(event.target || event.srcElement);

	if (!event.defaultPrevented) {
		forms.trigger(form.id + '.submit', [ form, event]);
	}

	if (!event.defaultPrevented) {
		forms.trigger('submit', [form, event]);
	}
});

gator.on('focus', '.mc4wp-form', function(event) {
	const form = forms.getByElement(event.target || event.srcElement);

	if( ! form.started ) {
		forms.trigger(form.id + '.started', [form, event]);
		forms.trigger('started', [form, event]);
		form.started = true;
	}
});

gator.on('change', '.mc4wp-form', function(event) {
	const form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('change', [form,event]);
	forms.trigger(form.id + '.change', [form,event]);
});

// init conditional elements
ConditionalElements.init();

// register early listeners
if( mc4wp.listeners ) {
	const listeners = mc4wp.listeners;
    for(let i=0; i<listeners.length;i++) {
        forms.on(listeners[i].event, listeners[i].callback);
    }

    // delete temp listeners array, so we don't bind twice
    delete mc4wp["listeners"];
}

// expose forms object
mc4wp.forms = forms;

// handle submitted form
if( config.submitted_form ) {
	const formConfig = config.submitted_form,
		element = document.getElementById(formConfig.element_id),
		form = forms.getByElement(element);

	handleFormRequest(form, formConfig.event, formConfig.errors, formConfig.data);
}

// expose mc4wp object globally
window.mc4wp = mc4wp;

