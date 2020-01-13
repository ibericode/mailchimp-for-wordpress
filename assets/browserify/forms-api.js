'use strict';

// deps & vars
const mc4wp = window.mc4wp || {};
const forms = require('./forms/forms.js');
const config = window.mc4wp_forms_config || {};
const scrollToElement = require('./misc/scroll-to-element.js');
import ConditionalElements from './forms/conditional-elements.js';

function handleFormRequest(form, eventName, errors, data){
	const timeStart = Date.now();
	const pageHeight = document.body.clientHeight;

	// re-populate form if an error occurred
	if (errors) {
		form.setData(data);
	}

	// scroll to form
	if( window.scrollY <= 10 && config.auto_scroll ) {
		scrollToElement(form.element);
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
			scrollToElement(form.element);
 		}
	});
}

function bind(evtName, cb) {
	document.addEventListener(evtName, evt => {
		if (!evt.target) {
			return;
		}

		let el = evt.target;
		if ( (!el.className || el.className.indexOf('mc4wp-form') === -1) && (!el.matches || !el.matches('.mc4wp-form *')) ) {
			return;
		}

		cb.call(evt, evt);
	}, true)
}

bind('submit', (event) => {
	const form = forms.getByElement(event.target);

	if (!event.defaultPrevented) {
		forms.trigger(form.id + '.submit', [ form, event]);
	}

	if (!event.defaultPrevented) {
		forms.trigger('submit', [form, event]);
	}
} );
bind('focus', (event) => {
	const form = forms.getByElement(event.target);

	if( ! form.started ) {
		forms.trigger(form.id + '.started', [form, event]);
		forms.trigger('started', [form, event]);
		form.started = true;
	}
});
bind('change', (event) => {
	const form = forms.getByElement(event.target);
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

