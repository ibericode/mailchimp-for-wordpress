const Form = require('./form.js')
const forms = []
const EventEmitter = require('./../events.js')
const events = new EventEmitter()

/**
 * Map legacy event names to native CustomEvent names.
 * @param {string} eventName
 * @returns {string}
 * @private
 */
function toNativeEventName (eventName) {
  const base = eventName.indexOf('.') > -1 ? eventName.split('.')[1] : eventName
  return 'mc4wp-' + base
}

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
 * Triggers two events. One namespaced to the specific form ID and one global (ie fires for all forms).
 * Also dispatches a native CustomEvent on the form element.
 *
 * @param {string} eventName The name of the event to trigger
 * @param {array} eventArgs Arguments to pass to registered event listeners. The first argument should be a Form object.
 * @public
 */
function trigger (eventName, eventArgs) {
  const form = eventArgs[0]
  const nativeName = toNativeEventName(eventName)
  const isSubmit = eventName === 'submit' || eventName.indexOf('.submit') > 0

  // legacy EventEmitter — fires immediately for submit, async for others
  if (isSubmit) {
    events.emit(form.id + '.' + eventName, eventArgs)
    events.emit(eventName, eventArgs)
  } else {
    window.setTimeout(function () {
      events.emit(form.id + '.' + eventName, eventArgs)
      events.emit(eventName, eventArgs)
    }, 10)
  }

  // native CustomEvent on the form DOM element
  if (form.element && form.element.dispatchEvent) {
    const detail = { form: form, data: eventArgs[1] || null }
    const customEvent = new CustomEvent(nativeName, {
      detail: detail,
      bubbles: true,
      cancelable: isSubmit
    })

    if (isSubmit) {
      form.element.dispatchEvent(customEvent)
    } else {
      window.setTimeout(function () {
        form.element.dispatchEvent(customEvent)
      }, 10)
    }
  }
}

/**
 * Add event listener via legacy API.
 *
 * @deprecated Use formElement.addEventListener('mc4wp-' + eventName, callback) instead.
 * @param {string} eventName
 * @param {function} callback
 */
function on (eventName, callback) {
  if (typeof console !== 'undefined' && console.warn) {
    console.warn(
      'Mailchimp for WordPress: mc4wp.forms.on() is deprecated. ' +
      'Use addEventListener() instead. ' +
      'See https://www.mc4wp.com/kb/javascript-form-events/'
    )
  }

  events.on(eventName, callback)
}

/**
 * Register a native DOM event listener on a specific form element.
 *
 * @param {string} formId The form ID (data-id attribute)
 * @param {string} eventName Event name without prefix (e.g. "success", "submit")
 * @param {function} callback
 */
function addEventListener (formId, eventName, callback) {
  const form = get(formId)
  if (form && form.element) {
    form.element.addEventListener('mc4wp-' + eventName, callback)
  }
}

module.exports = { get, getByElement, on, trigger, addEventListener }
