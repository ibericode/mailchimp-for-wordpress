function mc4wpAddEvent(element, eventName, callback) {
	if (element.addEventListener) {
		element.addEventListener(eventName, callback, false);
	} else {
		element.attachEvent("on" + eventName, callback);
	}
}

mc4wpAddEvent(window, "load", function() {
	
	/**
	* Populate the form elements in a given container from a JSON object
	*/
	function populateFields(container, data, basename) {

		for(var key in data) {

			var name = key;
			var value = data[key];

			// no need to set empty values
			if(value == "") {
				continue;
			}

			// handle array name attributes
			if(typeof(basename) !== "undefined") {
				name = basename + "[" + key + "]";
			}

			if(value.constructor == Array) {
				name += '[]';
			} else if(typeof value == "object") {
				populateFields(container, value, name);
				continue;
			}

			// populate field
			var elements = container.querySelectorAll('input[name="'+ name +'"], select[name="'+ name +'"], textarea[name="'+ name +'"]');
			
			// Dirty: abandon if we did not find the element
			if(!elements) { 
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
							element.checked = (element.value === value[j]);
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

	// scroll to submitted form element
	var formElement = document.getElementById('mc4wp-form-' + mc4wp.submittedFormId);

	if(!formElement) { 
		return; 
	}

	// only populate fields on error
	if(mc4wp.success == false) {
		populateFields(formElement, mc4wp.postData);
	}

	// calculate window height to scroll to
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
});