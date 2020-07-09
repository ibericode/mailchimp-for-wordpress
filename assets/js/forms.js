(function(){function r(e,n,t){function o(i,f){if(!n[i]){if(!e[i]){var c="function"==typeof require&&require;if(!f&&c)return c(i,!0);if(u)return u(i,!0);var a=new Error("Cannot find module '"+i+"'");throw a.code="MODULE_NOT_FOUND",a}var p=n[i]={exports:{}};e[i][0].call(p.exports,function(r){var n=e[i][1][r];return o(n||r)},p,p.exports,r,e,n,t)}return n[i].exports}for(var u="function"==typeof require&&require,i=0;i<t.length;i++)o(t[i]);return o}return r})()({1:[function(require,module,exports){
"use strict";

var mc4wp = window.mc4wp || {};

var forms = require('./forms/forms.js');

require('./forms/conditional-elements.js');

function trigger(event, args) {
  forms.trigger(args[0].id + '.' + event, args);
  forms.trigger(event, args);
}

function bind(evtName, cb) {
  document.addEventListener(evtName, function (evt) {
    if (!evt.target) {
      return;
    }

    var el = evt.target;
    var fireEvent = false;

    if (typeof el.className === 'string') {
      fireEvent = el.className.indexOf('mc4wp-form') > -1;
    }

    if (!fireEvent && typeof el.matches === 'function') {
      fireEvent = el.matches('.mc4wp-form *');
    }

    if (fireEvent) {
      cb.call(evt, evt);
    }
  }, true);
}

bind('submit', function (event) {
  var form = forms.getByElement(event.target);

  if (!event.defaultPrevented) {
    forms.trigger(form.id + '.submit', [form, event]);
  }

  if (!event.defaultPrevented) {
    forms.trigger('submit', [form, event]);
  }
});
bind('focus', function (event) {
  var form = forms.getByElement(event.target);

  if (!form.started) {
    trigger('started', [form, event]);
    form.started = true;
  }
});
bind('change', function (event) {
  var form = forms.getByElement(event.target);
  trigger('change', [form, event]);
}); // register early listeners

if (mc4wp.listeners) {
  var listeners = mc4wp.listeners;

  for (var i = 0; i < listeners.length; i++) {
    forms.on(listeners[i].event, listeners[i].callback);
  } // delete temp listeners array, so we don't bind twice


  delete mc4wp.listeners;
} // expose forms object


mc4wp.forms = forms; // expose mc4wp object globally

window.mc4wp = mc4wp;

},{"./forms/conditional-elements.js":2,"./forms/forms.js":4}],2:[function(require,module,exports){
"use strict";

function getFieldValues(form, fieldName) {
  var values = [];
  var inputs = form.querySelectorAll('input[name="' + fieldName + '"],select[name="' + fieldName + '"],textarea[name="' + fieldName + '"]');

  for (var i = 0; i < inputs.length; i++) {
    var input = inputs[i];

    if ((input.type === 'radio' || input.type === 'checkbox') && !input.checked) {
      continue;
    }

    values.push(input.value);
  }

  return values;
}

function findForm(element) {
  var bubbleElement = element;

  while (bubbleElement.parentElement) {
    bubbleElement = bubbleElement.parentElement;

    if (bubbleElement.tagName === 'FORM') {
      return bubbleElement;
    }
  }

  return null;
}

function toggleElement(el) {
  var show = !!el.getAttribute('data-show-if');
  var conditions = show ? el.getAttribute('data-show-if').split(':') : el.getAttribute('data-hide-if').split(':');
  var fieldName = conditions[0];
  var expectedValues = (conditions.length > 1 ? conditions[1] : '*').split('|');
  var form = findForm(el);
  var values = getFieldValues(form, fieldName); // determine whether condition is met

  var conditionMet = false;

  for (var i = 0; i < values.length; i++) {
    var value = values[i]; // condition is met when value is in array of expected values OR expected values contains a wildcard and value is not empty

    conditionMet = expectedValues.indexOf(value) > -1 || expectedValues.indexOf('*') > -1 && value.length > 0;

    if (conditionMet) {
      break;
    }
  } // toggle element display


  if (show) {
    el.style.display = conditionMet ? '' : 'none';
  } else {
    el.style.display = conditionMet ? 'none' : '';
  } // find all inputs inside this element and toggle [required] attr (to prevent HTML5 validation on hidden elements)


  var inputs = el.querySelectorAll('input,select,textarea');
  [].forEach.call(inputs, function (el) {
    if ((conditionMet || show) && el.getAttribute('data-was-required')) {
      el.required = true;
      el.removeAttribute('data-was-required');
    }

    if ((!conditionMet || !show) && el.required) {
      el.setAttribute('data-was-required', 'true');
      el.required = false;
    }
  });
} // evaluate conditional elements globally


function evaluate() {
  var elements = document.querySelectorAll('.mc4wp-form [data-show-if],.mc4wp-form [data-hide-if]');
  [].forEach.call(elements, toggleElement);
} // re-evaluate conditional elements for change events on forms


function handleInputEvent(evt) {
  if (!evt.target || !evt.target.form || evt.target.form.className.indexOf('mc4wp-form') < 0) {
    return;
  }

  var form = evt.target.form;
  var elements = form.querySelectorAll('[data-show-if],[data-hide-if]');
  [].forEach.call(elements, toggleElement);
}

document.addEventListener('keyup', handleInputEvent, true);
document.addEventListener('change', handleInputEvent, true);
document.addEventListener('mc4wp-refresh', evaluate, true);
window.addEventListener('load', evaluate);
evaluate();

},{}],3:[function(require,module,exports){
"use strict";

var serialize = require('form-serialize');

var populate = require('populate.js');

var Form = function Form(id, element) {
  this.id = id;
  this.element = element || document.createElement('form');
  this.name = this.element.getAttribute('data-name') || 'Form #' + this.id;
  this.errors = [];
  this.started = false;
};

Form.prototype.setData = function (data) {
  try {
    populate(this.element, data);
  } catch (e) {
    console.error(e);
  }
};

Form.prototype.getData = function () {
  return serialize(this.element, {
    hash: true,
    empty: true
  });
};

Form.prototype.getSerializedData = function () {
  return serialize(this.element, {
    hash: false,
    empty: true
  });
};

Form.prototype.setResponse = function (msg) {
  this.element.querySelector('.mc4wp-response').innerHTML = msg;
}; // revert back to original state


Form.prototype.reset = function () {
  this.setResponse('');
  this.element.querySelector('.mc4wp-form-fields').style.display = '';
  this.element.reset();
};

module.exports = Form;

},{"form-serialize":5,"populate.js":6}],4:[function(require,module,exports){
"use strict";

var Form = require('./form.js');

var forms = [];
var listeners = {};

function emit(event, args) {
  listeners[event] = listeners[event] || [];
  listeners[event].forEach(function (f) {
    return f.apply(null, args);
  });
}

function on(event, func) {
  listeners[event] = listeners[event] || [];
  listeners[event].push(func);
}

function off(event, func) {
  listeners[event] = listeners[event] || [];
  listeners[event] = listeners[event].filter(function (f) {
    return f !== func;
  });
} // get form by its id
// please note that this will get the FIRST occurence of the form with that ID on the page


function get(formId) {
  formId = parseInt(formId); // do we have form for this one already?

  for (var i = 0; i < forms.length; i++) {
    if (forms[i].id === formId) {
      return forms[i];
    }
  } // try to create from first occurence of this element


  var formElement = document.querySelector('.mc4wp-form-' + formId);
  return createFromElement(formElement, formId);
} // get form by <form> element (or any input in form)


function getByElement(element) {
  var formElement = element.form || element;

  for (var i = 0; i < forms.length; i++) {
    if (forms[i].element === formElement) {
      return forms[i];
    }
  }

  return createFromElement(formElement);
} // create form object from <form> element


function createFromElement(formElement, id) {
  id = id || parseInt(formElement.getAttribute('data-id')) || 0;
  var form = new Form(id, formElement);
  forms.push(form);
  return form;
}

function trigger(eventName, eventArgs) {
  if (eventName === 'submit' || eventName.indexOf('.submit') > 0) {
    // don't spin up new thread for submit event as we want to preventDefault()...
    emit(eventName, eventArgs);
  } else {
    // process in separate thread to prevent errors from breaking core functionality
    window.setTimeout(function () {
      emit(eventName, eventArgs);
    }, 1);
  }
}

module.exports = {
  get: get,
  getByElement: getByElement,
  on: on,
  off: off,
  trigger: trigger
};

},{"./form.js":3}],5:[function(require,module,exports){
// get successful control from form and assemble into object
// http://www.w3.org/TR/html401/interact/forms.html#h-17.13.2

// types which indicate a submit action and are not successful controls
// these will be ignored
var k_r_submitter = /^(?:submit|button|image|reset|file)$/i;

// node names which could be successful controls
var k_r_success_contrls = /^(?:input|select|textarea|keygen)/i;

// Matches bracket notation.
var brackets = /(\[[^\[\]]*\])/g;

// serializes form fields
// @param form MUST be an HTMLForm element
// @param options is an optional argument to configure the serialization. Default output
// with no options specified is a url encoded string
//    - hash: [true | false] Configure the output type. If true, the output will
//    be a js object.
//    - serializer: [function] Optional serializer function to override the default one.
//    The function takes 3 arguments (result, key, value) and should return new result
//    hash and url encoded str serializers are provided with this module
//    - disabled: [true | false]. If true serialize disabled fields.
//    - empty: [true | false]. If true serialize empty fields
function serialize(form, options) {
    if (typeof options != 'object') {
        options = { hash: !!options };
    }
    else if (options.hash === undefined) {
        options.hash = true;
    }

    var result = (options.hash) ? {} : '';
    var serializer = options.serializer || ((options.hash) ? hash_serializer : str_serialize);

    var elements = form && form.elements ? form.elements : [];

    //Object store each radio and set if it's empty or not
    var radio_store = Object.create(null);

    for (var i=0 ; i<elements.length ; ++i) {
        var element = elements[i];

        // ingore disabled fields
        if ((!options.disabled && element.disabled) || !element.name) {
            continue;
        }
        // ignore anyhting that is not considered a success field
        if (!k_r_success_contrls.test(element.nodeName) ||
            k_r_submitter.test(element.type)) {
            continue;
        }

        var key = element.name;
        var val = element.value;

        // we can't just use element.value for checkboxes cause some browsers lie to us
        // they say "on" for value when the box isn't checked
        if ((element.type === 'checkbox' || element.type === 'radio') && !element.checked) {
            val = undefined;
        }

        // If we want empty elements
        if (options.empty) {
            // for checkbox
            if (element.type === 'checkbox' && !element.checked) {
                val = '';
            }

            // for radio
            if (element.type === 'radio') {
                if (!radio_store[element.name] && !element.checked) {
                    radio_store[element.name] = false;
                }
                else if (element.checked) {
                    radio_store[element.name] = true;
                }
            }

            // if options empty is true, continue only if its radio
            if (val == undefined && element.type == 'radio') {
                continue;
            }
        }
        else {
            // value-less fields are ignored unless options.empty is true
            if (!val) {
                continue;
            }
        }

        // multi select boxes
        if (element.type === 'select-multiple') {
            val = [];

            var selectOptions = element.options;
            var isSelectedOptions = false;
            for (var j=0 ; j<selectOptions.length ; ++j) {
                var option = selectOptions[j];
                var allowedEmpty = options.empty && !option.value;
                var hasValue = (option.value || allowedEmpty);
                if (option.selected && hasValue) {
                    isSelectedOptions = true;

                    // If using a hash serializer be sure to add the
                    // correct notation for an array in the multi-select
                    // context. Here the name attribute on the select element
                    // might be missing the trailing bracket pair. Both names
                    // "foo" and "foo[]" should be arrays.
                    if (options.hash && key.slice(key.length - 2) !== '[]') {
                        result = serializer(result, key + '[]', option.value);
                    }
                    else {
                        result = serializer(result, key, option.value);
                    }
                }
            }

            // Serialize if no selected options and options.empty is true
            if (!isSelectedOptions && options.empty) {
                result = serializer(result, key, '');
            }

            continue;
        }

        result = serializer(result, key, val);
    }

    // Check for all empty radio buttons and serialize them with key=""
    if (options.empty) {
        for (var key in radio_store) {
            if (!radio_store[key]) {
                result = serializer(result, key, '');
            }
        }
    }

    return result;
}

function parse_keys(string) {
    var keys = [];
    var prefix = /^([^\[\]]*)/;
    var children = new RegExp(brackets);
    var match = prefix.exec(string);

    if (match[1]) {
        keys.push(match[1]);
    }

    while ((match = children.exec(string)) !== null) {
        keys.push(match[1]);
    }

    return keys;
}

function hash_assign(result, keys, value) {
    if (keys.length === 0) {
        result = value;
        return result;
    }

    var key = keys.shift();
    var between = key.match(/^\[(.+?)\]$/);

    if (key === '[]') {
        result = result || [];

        if (Array.isArray(result)) {
            result.push(hash_assign(null, keys, value));
        }
        else {
            // This might be the result of bad name attributes like "[][foo]",
            // in this case the original `result` object will already be
            // assigned to an object literal. Rather than coerce the object to
            // an array, or cause an exception the attribute "_values" is
            // assigned as an array.
            result._values = result._values || [];
            result._values.push(hash_assign(null, keys, value));
        }

        return result;
    }

    // Key is an attribute name and can be assigned directly.
    if (!between) {
        result[key] = hash_assign(result[key], keys, value);
    }
    else {
        var string = between[1];
        // +var converts the variable into a number
        // better than parseInt because it doesn't truncate away trailing
        // letters and actually fails if whole thing is not a number
        var index = +string;

        // If the characters between the brackets is not a number it is an
        // attribute name and can be assigned directly.
        if (isNaN(index)) {
            result = result || {};
            result[string] = hash_assign(result[string], keys, value);
        }
        else {
            result = result || [];
            result[index] = hash_assign(result[index], keys, value);
        }
    }

    return result;
}

// Object/hash encoding serializer.
function hash_serializer(result, key, value) {
    var matches = key.match(brackets);

    // Has brackets? Use the recursive assignment function to walk the keys,
    // construct any missing objects in the result tree and make the assignment
    // at the end of the chain.
    if (matches) {
        var keys = parse_keys(key);
        hash_assign(result, keys, value);
    }
    else {
        // Non bracket notation can make assignments directly.
        var existing = result[key];

        // If the value has been assigned already (for instance when a radio and
        // a checkbox have the same name attribute) convert the previous value
        // into an array before pushing into it.
        //
        // NOTE: If this requirement were removed all hash creation and
        // assignment could go through `hash_assign`.
        if (existing) {
            if (!Array.isArray(existing)) {
                result[key] = [ existing ];
            }

            result[key].push(value);
        }
        else {
            result[key] = value;
        }
    }

    return result;
}

// urlform encoding serializer
function str_serialize(result, key, value) {
    // encode newlines as \r\n cause the html spec says so
    value = value.replace(/(\r)?\n/g, '\r\n');
    value = encodeURIComponent(value);

    // spaces should be '+' rather than '%20'.
    value = value.replace(/%20/g, '+');
    return result + (result ? '&' : '') + encodeURIComponent(key) + '=' + value;
}

module.exports = serialize;

},{}],6:[function(require,module,exports){

/**
 * Populate form fields from a JSON object.
 *
 * @param form object The form element containing your input fields.
 * @param data array JSON data to populate the fields with.
 * @param basename string Optional basename which is added to `name` attributes
 */
function populate(form, data, basename) {
	for (var key in data) {
		if (! data.hasOwnProperty(key)) {
			continue;
		}

		var name = key;
		var value = data[key];

        if ('undefined' === typeof value) {
            value = '';
        }

        if (null === value) {
            value = '';
        }

		// handle array name attributes
		if (typeof(basename) !== "undefined") {
			name = basename + "[" + key + "]";
		}

		if (value.constructor === Array) {
			name += '[]';
		} else if(typeof value == "object") {
			populate(form, value, name);
			continue;
		}

		// only proceed if element is set
		var element = form.elements.namedItem(name);
		if (! element) {
			continue;
		}

		var type = element.type || element[0].type;

		switch(type ) {
			default:
				element.value = value;
				break;

			case 'radio':
			case 'checkbox':
				var values = value.constructor === Array ? value : [value];
				for (var j=0; j < element.length; j++) {
					element[j].checked = values.indexOf(element[j].value) > -1;
				}
				break;

			case 'select-multiple':
				var values = value.constructor === Array ? value : [value];
				for(var k = 0; k < element.options.length; k++) {
					element.options[k].selected = (values.indexOf(element.options[k].value) > -1 );
				}
				break;

			case 'select':
			case 'select-one':
				element.value = value.toString() || value;
				break;

			case 'date':
      			element.value = new Date(value).toISOString().split('T')[0];	
				break;
		}

	}
};

if (typeof module !== 'undefined' && module.exports) {
	module.exports = populate;
} 
},{}]},{},[1]);
