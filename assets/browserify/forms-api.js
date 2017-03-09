'use strict';

// deps & vars
var mc4wp = window.mc4wp || {};
var Gator = require('gator');
var forms = require('./forms/forms.js');
var config = window.mc4wp_forms_config || {};



// funcs
function scrollToForm(form) {
	var animate = config.auto_scroll === 'animated';
	var args = {
		behavior: animate ? "smooth" : "instant"
	};
	form.element.scrollIntoView(args);
}

function handleFormRequest(form, action, errors, data){
	var pageHeight = document.body.clientHeight;
	var timeStart = Date.now();

	// re-populate form
	if( errors ) {
		form.setData(data);
	}

	if( config.auto_scroll ) {
		scrollToForm(form);
	}

	// trigger events on window.load so all other scripts have loaded
	window.addEventListener('load', function() {
		var timeElapsed = Date.now() - timeStart;

		// scroll to form again if page height changed since last scroll
		// (only if load didn't take more than 0.8 seconds to prevent overtaking user scroll)
		if( config.auto_scroll && timeElapsed < 800 && document.body.clientHeight != pageHeight ) {
			scrollToForm(form);
		}

		// trigger events
		forms.trigger('submitted', [form]);
		forms.trigger(form.id + '.submitted', [form]);

		if( errors ) {
			forms.trigger('error', [form, errors]);
			forms.trigger(form.id + '.error', [form, errors]);
		} else {
			// form was successfully submitted
			forms.trigger('success', [form, data]);
			forms.trigger(form.id + ',success', [form, data]);

			// subscribed / unsubscribed
			forms.trigger(action + "d", [form, data]);
			forms.trigger(form.id + "." + action + "d", [form, data]);
		}
	});
}

function toggleElement(el, expectedValue, show ) {
	return function() {
		var value = this.value.trim();
		var checked = ( this.getAttribute('type') !== 'radio' && this.getAttribute('type') !== 'checked' ) || this.checked;
		var conditionMet = checked && ( ( value === expectedValue && expectedValue !== "" ) || ( expectedValue === "" && value.length > 0 ) );
		if(show){
			el.style.display = ( conditionMet ) ? '' : 'none';
		}else{
			el.style.display = ( conditionMet ) ? 'none' : '';
		}
	}
}

function toggleConditionalElements() {
	var input = this;
	var elements = input.form.querySelectorAll('[data-show-if], [data-hide-if]');
	var inputName = (input.getAttribute('name') || '').toLowerCase();

	[].forEach.call(elements, function(el) {
		var show = !!el.getAttribute('data-show-if');
		var conditions = show ? el.getAttribute('data-show-if').split(':') : el.getAttribute('data-hide-if').split(':');
		var nameCondition = conditions[0];
        var valueCondition = conditions[1] || "";

        if (inputName !== nameCondition.toLowerCase() ) {
			return;
    	}

		var callback = toggleElement(el, valueCondition, show);
		callback.call(input);
	});
}

Gator(document.body).on('keyup', '.mc4wp-form input, .mc4wp-form textarea, .mc4wp-form select', toggleConditionalElements);
Gator(document.body).on('change', '.mc4wp-form input, .mc4wp-form textarea, .mc4wp-form select', toggleConditionalElements);
window.addEventListener('load', function() {
    [].forEach.call( document.querySelectorAll('.mc4wp-form input, .mc4wp-form textarea, .mc4wp-form select'), function(el) {
        toggleConditionalElements.call(el);
    });
});

// Bind browser events to form events (using delegation)
Gator(document.body).on('submit', '.mc4wp-form', function(event) {
	var form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('submit', [form, event]);
	forms.trigger(form.id + '.submit', [ form, event]);
});

Gator(document.body).on('focus', '.mc4wp-form', function(event) {
	var form = forms.getByElement(event.target || event.srcElement);

	if( ! form.started ) {
		forms.trigger('started', [form, event]);
		forms.trigger(form.id + '.started', [form, event]);
		form.started = true;
	}
});

Gator(document.body).on('change', '.mc4wp-form', function(event) {
	var form = forms.getByElement(event.target || event.srcElement);
	forms.trigger('change', [form,event]);
	forms.trigger(form.id + '.change', [form,event]);
});

// register early listeners
if( mc4wp.listeners ) {
    var listeners = mc4wp.listeners;
    for(var i=0; i<listeners.length;i++) {
        forms.on(listeners[i].event, listeners[i].callback);
    }

    // delete temp listeners array, so we don't bind twice
    delete mc4wp["listeners"];
}

// expose forms object
mc4wp.forms = forms;

// handle submitted form
if( config.submitted_form ) {
	var formConfig = config.submitted_form,
		element = document.getElementById(formConfig.element_id),
		form = forms.getByElement(element);

	handleFormRequest(form, formConfig.action, formConfig.errors, formConfig.data);
}

// expose mc4wp object globally
window.mc4wp = mc4wp;

