const Form = require('./form.js')
const forms = []
const EventEmitter = require('./../events.js')
const events = new EventEmitter()

/**
 * Get a Form object by its ID.
 * This will return the first occurence of the form with the given ID on the page.
 * @param {string|int} formId
 * @returns {Form}
 */
function get (formId) {
  formId = parseInt(formId)

  // do we have form for this one already?
  for (let i = 0; i < forms.length; i++) {
    if (forms[i].id === formId) {
      return forms[i]
    }
  }

  // try to create from first occurence of this element
  const formElement = document.querySelector('.mc4wp-form-' + formId)
  return createFromElement(formElement, formId)
}

/**
 * Get form object from its HTML element (or from input field inside form element)
 *
 * @param {HTMLElement|HTMLInputElement} element
 * @returns {Form}
 */
function getByElement (element) {
  const formElement = element.form || element

  for (let i = 0; i < forms.length; i++) {
    if (forms[i].element === formElement) {
      return forms[i]
    }
  }

  return createFromElement(formElement)
}

// create form object from <form> element
function createFromElement (formElement, id) {
  id = id || parseInt(formElement.getAttribute('data-id')) || 0
  const form = new Form(id, formElement)
  forms.push(form)
  return form
}

/**
 * Triggers two events. One namespaced to the specific form ID and one global (ie fires for all forms)
 *
 * @param {string} eventName The name of the event to trigger
 * @param {array} eventArgs Arguments to pass to registered event listeners. The first argument should be a Form object.
 * @public
 */
function trigger (eventName, eventArgs) {
  if (eventName === 'submit' || eventName.indexOf('.submit') > 0) {
    // don't spin up new thread for submit event as we want to preventDefault()...
    events.emit(eventArgs[0].id + '.' + eventName, eventArgs)
    events.emit(eventName, eventArgs)
  } else {
    // process in separate thread to prevent errors from breaking core functionality
    window.setTimeout(function () {
      events.emit(eventArgs[0].id + '.' + eventName, eventArgs)
      events.emit(eventName, eventArgs)
    }, 10)
  }
}

/**
 * Add event listener.
 * For a list of valid event names, please see https://www.mc4wp.com/kb/javascript-form-events/.
 * @param {string} eventName
 * @param {function} callback
 */
function on (eventName, callback) {
  events.on(eventName, callback)
}

module.exports = { get, getByElement, on, trigger }
