'use strict';

const showIfElements = document.querySelectorAll('[data-showif]');

[].forEach.call( showIfElements, function(element) {
	let config = JSON.parse( element.getAttribute('data-showif') );
	let parentElements = document.querySelectorAll('[name="'+ config.element +'"]');
	let inputs = element.querySelectorAll('input,select,textarea:not([readonly])');
	let hide = config.hide === undefined || config.hide;

	function toggleElement() {

		// do nothing with unchecked radio inputs
		if( this.getAttribute('type') === "radio" && ! this.checked ) {
			return;
		}

		let value = ( this.getAttribute("type")  === "checkbox" ) ? this.checked : this.value;
		let conditionMet = ( value == config.value );

		if( hide ) {
			element.style.display = conditionMet ? '' : 'none';
			element.style.visibility = conditionMet ? '' : 'hidden';
		} else {
			element.style.opacity = conditionMet ? '' : '0.4';
		}

		// disable input fields to stop sending their values to server
		[].forEach.call( inputs, function(inputElement) {
			conditionMet ? inputElement.removeAttribute('readonly') : inputElement.setAttribute('readonly', 'readonly');
		});
	}

	// find checked element and call toggleElement function
	[].forEach.call( parentElements, function(el) {
		el.addEventListener('change', toggleElement);
		toggleElement.call(el);
	});
});
