const mc4wp = window.mc4wp || {}
const forms = require('./forms/forms.js')
require('./forms/conditional-elements.js')

/**
 * Binds event to document but only fires if event was triggered inside a .mc4wp-form element
 * @param {string} evtName
 * @param {function} cb
 * @private
 */
function bind (evtName, cb) {
  document.addEventListener(evtName, evt => {
    if (!evt.target) {
      return
    }

    const el = evt.target
    const fireEvent = (typeof el.className === 'string' && el.className.indexOf('mc4wp-form') > -1) || (typeof el.matches === 'function' && el.matches('.mc4wp-form *'))
    if (fireEvent) {
      cb.call(evt, evt)
    }
  }, true)
}

/**
 * Handles 'submit' events for any .mc4wp-form element
 * @param {Event} evt
 * @private
 */
function onSubmit (evt) {
  if (evt.defaultPrevented) {
    return
  }

  const form = forms.getByElement(evt.target)
  if (!evt.defaultPrevented) {
    forms.trigger('submit', [form, evt])
  }
}

/**
 * Handles 'focus' events for any relevant element inside a .mc4wp-form
 * @param {Event} evt
 * @private
 */
function onFocus (evt) {
  const form = forms.getByElement(evt.target)
  if (!form.started) {
    forms.trigger('started', [form, evt])
    form.started = true
  }
}

/**
 * Handles 'change' events for any relevant element inside a .mc4wp-form
 * @param {Event} evt
 * @private
 */
function onChange (evt) {
  const form = forms.getByElement(evt.target)
  forms.trigger('change', [form, evt])
}

/**
 * Copies over listeners which were added before this script was loaded
 * These are stored in a temporary variable called `mc4wp.listeners` which we print very early on in wp_head().
 * @param {object} lstnr
 * @private
 */
function registerListener (lstnr) {
  forms.on(lstnr.event, lstnr.callback)
}

// bind event listeners
bind('submit', onSubmit)
bind('focus', onFocus)
bind('change', onChange)

// register early listeners
if (mc4wp.listeners) {
  [].forEach.call(mc4wp.listeners, registerListener)
  delete mc4wp.listeners
}

// expose forms object
mc4wp.forms = forms

// expose mc4wp object globally
window.mc4wp = mc4wp
