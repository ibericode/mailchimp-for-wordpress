window.mc4wpFormRequest = (function() {

	var formElement;

	/**
	 * Initializes the stuff to do
	 */
	function init() {
		addEvent( window, 'load', act );
	}

	function act() {

		// Find element of submitted form
		formElement = document.getElementById('mc4wp-form-' + mc4wpFormRequestData.submittedFormId);
		if( ! formElement ) {
			return;
		}

		// If an error occured, re-populate form fields
		// only populate fields on error
		if( mc4wpFormRequestData.success == false ) {
			populateFields( formElement, mc4wpFormRequestData.postData );
		}

		// Scroll to form element
		var scrollToHeight = 0;
		var obj = formElement;
		var windowHeight = window.innerHeight;

		if (obj.offsetParent) {
			do {
				scrollToHeight += obj.offsetTop;
			} while (obj = obj.offsetParent);
		} else {
			scrollToHeight = formElement.offsetTop;
		}

		if((windowHeight - 80) > formElement.clientHeight) {
			// vertically center the form, but only if there's enough space for a decent margin
			scrollToHeight = scrollToHeight - ((windowHeight - formElement.clientHeight) / 2);
		} else {
			// the form doesn't fit, scroll a little above the form
			scrollToHeight = scrollToHeight - 80;
		}

		// scroll there. if jQuery is loaded, do it with an animation.
		if(window.jQuery !== undefined) {
			jQuery('html, body').animate({ scrollTop: scrollToHeight }, 800);
		} else {
			window.scrollTo(0, scrollToHeight);
		}

	}

	/**
	 * Adds a browser event, IE compatible.
	 *
	 * @param element
	 * @param eventName
	 * @param callback
	 */
	function addEvent( element, eventName, callback ) {
		if (element.addEventListener) {
			element.addEventListener(eventName, callback, false);
		} else {
			element.attachEvent("on" + eventName, callback);
		}
	}

	/**
	 * Populate the form elements in a given container from a JSON object
	 *
	 * @param DOMElement container
	 * @param object data
	 * @param string basename
	 */
	function populateFields( container, data, basename ) {

		for( var key in data ) {

			var name = key;
			var value = data[key];

			// no need to set empty values
			if( value == '' ) {
				continue;
			}

			// handle array name attributes
			if(typeof(basename) !== "undefined") {
				name = basename + "[" + key + "]";
			}

			if( value.constructor == Array ) {
				name += '[]';
			} else if(typeof value == "object") {
				populateFields(container, value, name);
				continue;
			}

			// populate field
			var elements = container.querySelectorAll('input[name="'+ name +'"], select[name="'+ name +'"], textarea[name="'+ name +'"]');

			// Dirty: abandon if we did not find the element
			if( ! elements ) {
				return;
			}

			// loop through found elements to set their values
			for(var i = 0; i < elements.length; i++) {

				var element = elements[i];

				// check element type
				switch(element.type || element.tagName) {
					case 'text':
					case 'email':
					case 'date':
					case 'tel':
						element.value = value;

						// remove IE placeholder fallback class
						element.className = element.className.replace('placeholdersjs','');
						break;

					case 'radio':
						element.checked = (element.value === value);
						break;

					case 'checkbox':
						for(var j = 0; j < value.length; j++) {

							// check element if its in the values array
							var checked = (element.value === value[j]);
							if( checked ) {
								element.checked = (element.value === value[j]);
								break;
							}

							// uncheck if it isn't
							element.checked = false;
						}
						break;

					case 'select-multiple':
						var values = value.constructor == Array ? value : [value];

						for(var k = 0; k < element.options.length; k++)
						{
							for(var l = 0; l < values.length; l++)
							{
								element.options[k].selected |= (element.options[k].value == values[l]);
							}
						}
						break;

					case 'select':
					case 'select-one':
						element.value = value.toString() || value;
						break;
				}
			}


		}

	}

	return {
		init: function() {
			init();
		}
	}

})();
mc4wpFormRequest.init();