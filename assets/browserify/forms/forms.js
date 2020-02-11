const Form = require('./form.js')
const forms = []
const listeners = {}

function emit (event, args) {
  listeners[event] = listeners[event] || []
  listeners[event].forEach(f => f.apply(null, args))
}

function on (event, func) {
  listeners[event] = listeners[event] || []
  listeners[event].push(func)
}

function off (event, func) {
  listeners[event] = listeners[event] || []
  listeners[event] = listeners[event].filter(f => f !== func)
}

// get form by its id
// please note that this will get the FIRST occurence of the form with that ID on the page
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

// get form by <form> element (or any input in form)
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

function trigger (eventName, eventArgs) {
  if (eventName === 'submit' || eventName.indexOf('.submit') > 0) {
    // don't spin up new thread for submit event as we want to preventDefault()...
    emit(eventName, eventArgs)
  } else {
    // process in separate thread to prevent errors from breaking core functionality
    window.setTimeout(function () {
      emit(eventName, eventArgs)
    }, 1)
  }
}

module.exports = { get, getByElement, on, off, trigger }
