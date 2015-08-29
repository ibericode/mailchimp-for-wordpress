(function() {

	/**
	 * A formrequest. Should be passed an array of data in the following format.
	 *
	 * {
	 * 	formElementId: 0,
	 * 	success: 1,
	 * 	data: {}
	 * }
	 *
	 * @param request
	 */
	var formRequest = function( request ) {

		// vars
		var self = this;

		// Functions
		function init() {

			self.element = document.getElementById( request.formElementId );

			if( ! self.element ) {
				return false;
			}

			if( request.success != 1 ) {
				self.repopulate();
			}

			self.scrollTo();
		}

		/**
		 * Scrolls to the form element
		 */
		this.scrollTo = function() {
			// Scroll to form element
			var formElement = self.element;
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
			if( request.animate_scroll && window.jQuery !== undefined) {
				jQuery('html, body').animate({ scrollTop: scrollToHeight }, 800);
			} else {
				window.scrollTo(0, scrollToHeight);
			}
		};

		/**
		 * Repopulates the form fields
		 */
		this.repopulate = function() {
			populate( self.element, request.data );
		};

		// Call "init" on window.load event
		addEvent( window, 'load', init );
	};

	window.mc4wpFormRequest = new formRequest( mc4wpFormRequestData );

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

	/*! populate.js v1.0 by @dannyvankooten | MIT license */
	;(function(root) {

		/**
		 * Populate form fields from a JSON object.
		 *
		 * @param container object The element containing your input fields.
		 * @param data array JSON data to populate the fields with.
		 * @param basename string Optional basename which is added to `name` attributes
		 */
		var populate = function(container, data, basename) {

			for(var key in data) {

				if( ! data.hasOwnProperty( key ) ) {
					continue;
				}

				var name = key;
				var value = data[key];

				// handle array name attributes
				if(typeof(basename) !== "undefined") {
					name = basename + "[" + key + "]";
				}

				if(value.constructor === Array) {
					name += '[]';
				} else if(typeof value == "object") {
					populate(container, value, name);
					continue;
				}

				// find field element
				var elements = container.querySelectorAll('input[name="'+ name +'"], select[name="'+ name +'"], textarea[name="'+ name +'"]');

				// loop through elements to set their values
				for(var i = 0; i < elements.length; i++) {

					var element = elements[i];

					// check element type
					switch(element.type || element.tagName) {
						default:
							element.value = value;
							break;

						case 'radio':
							element.checked = (element.value === value);
							break;

						case 'checkbox':
							element.checked = ( value.indexOf(element.value) > -1 );
							break;

						case 'select-multiple':
							var values = value.constructor == Array ? value : [value];

							for(var k = 0; k < element.options.length; k++) {
								element.options[k].selected |= (values.indexOf(element.options[k].value) > -1 );
							}
							break;

						case 'select':
						case 'select-one':
							element.value = value.toString() || value;
							break;
					}
				}


			}

		};

		// Play nice with AMD, CommonJS or a plain global object.
		if ( typeof define == 'function' && typeof define.amd == 'object' && define.amd ) {
			define(function() {
				return populate;
			});
		}	else if ( typeof exports === 'object' ) {
			exports.populate = populate;
		} else {
			root.populate = populate;
		}

	}(this));

})();