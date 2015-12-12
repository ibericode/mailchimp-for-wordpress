'use strict';

var helpers = {};

helpers.toggleElement = function(selector) {
	var elements = document.querySelectorAll(selector);
	for( var i=0; i<elements.length;i++){
		var show = elements[i].clientHeight <= 0;
		elements[i].style.display = show ? '' : 'none';
	}
};

helpers.bindEventToElement = function(element,event,handler) {
	if ( element.addEventListener) {
		element.addEventListener(event, handler);
	} else if (element.attachEvent)  {
		element.attachEvent('on' + event, handler);
	}
};

helpers.bindEventToElements = function( elements, event, handler ) {
	Array.prototype.forEach.call( elements, function(element) {
		helpers.bindEventToElement(element,event,handler);
	});
};


// polling
helpers.debounce = function(func, wait, immediate) {
	var timeout;
	return function() {
		var context = this, args = arguments;
		var later = function() {
			timeout = null;
			if (!immediate) func.apply(context, args);
		};
		var callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(context, args);
	};
};

/**
 * Showif.js
 */
(function() {
	var showIfElements = document.querySelectorAll('[data-showif]');

	// dependent elements
	Array.prototype.forEach.call( showIfElements, function(element) {
		var config = JSON.parse( element.getAttribute('data-showif') );
		var parentElements = document.querySelectorAll('[name="'+ config.element +'"]');
		var inputs = element.querySelectorAll('input');

		function toggleElement() {

			// do nothing with unchecked elements
			if( typeof( this.checked ) === "boolean" && ! this.checked ) {
				return;
			}

			// check if element value matches expected value
			var conditionMet = ( this.value == config.value );
			element.style.display = conditionMet ? '' : 'none';
			element.style.visibility = conditionMet ? '' : 'hidden';

			// disable input fields to stop sending their values to server
			Array.prototype.forEach.call( inputs, function(inputElement) {
				inputElement.disabled = !conditionMet;
			});
		}

		// find checked element and call toggleElement function
		Array.prototype.forEach.call( parentElements, function( parentElement ) {
			toggleElement.call(parentElement);
		});

		// bind on all changes
		helpers.bindEventToElements(parentElements, 'change', toggleElement);
	});
})();

module.exports = helpers;