const mc4wp = window.mc4wp || {}
const forms = require('./forms/forms.js')
require('./forms/conditional-elements.js')

function trigger (event, args) {
  forms.trigger(args[0].id + '.' + event, args)
  forms.trigger(event, args)
}

// binds event to document but only fire if event was triggered inside a .mc4wp-form element
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

function onSubmit (evt) {
  if (evt.defaultPrevented) {
    return
  }

  const form = forms.getByElement(evt.target)
  if (!evt.defaultPrevented) {
    forms.trigger(form.id + '.submit', [form, evt])
  }

  if (!evt.defaultPrevented) {
    forms.trigger('submit', [form, evt])
  }
}

function onFocus (evt) {
  const form = forms.getByElement(evt.target)
  if (!form.started) {
    trigger('started', [form, evt])
    form.started = true
  }
}

function onChange (evt) {
  const form = forms.getByElement(evt.target)
  trigger('change', [form, evt])
}

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
