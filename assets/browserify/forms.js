const mc4wp = window.mc4wp || {}
const forms = require('./forms/forms.js')
require('./forms/conditional-elements.js')

function trigger (event, args) {
  forms.trigger(args[0].id + '.' + event, args)
  forms.trigger(event, args)
}

function bind (evtName, cb) {
  document.addEventListener(evtName, evt => {
    if (!evt.target) {
      return
    }

    const el = evt.target
    let fireEvent = false

    if (typeof el.className === 'string') {
      fireEvent = el.className.indexOf('mc4wp-form') > -1
    }

    if (!fireEvent && typeof el.matches === 'function') {
      fireEvent = el.matches('.mc4wp-form *')
    }

    if (fireEvent) {
      cb.call(evt, evt)
    }
  }, true)
}

bind('submit', (event) => {
  const form = forms.getByElement(event.target)

  if (!event.defaultPrevented) {
    forms.trigger(form.id + '.submit', [form, event])
  }

  if (!event.defaultPrevented) {
    forms.trigger('submit', [form, event])
  }
})
bind('focus', (event) => {
  const form = forms.getByElement(event.target)
  if (!form.started) {
    trigger('started', [form, event])
    form.started = true
  }
})
bind('change', (event) => {
  const form = forms.getByElement(event.target)
  trigger('change', [form, event])
})

// register early listeners
if (mc4wp.listeners) {
  const listeners = mc4wp.listeners
  for (let i = 0; i < listeners.length; i++) {
    forms.on(listeners[i].event, listeners[i].callback)
  }

  // delete temp listeners array, so we don't bind twice
  delete mc4wp.listeners
}

// expose forms object
mc4wp.forms = forms

// expose mc4wp object globally
window.mc4wp = mc4wp
