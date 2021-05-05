import scrollToElement from './misc/scroll-to-element.js'
const submittedForm = window.mc4wp_submitted_form
const forms = window.mc4wp.forms

function trigger (event, args) {
  forms.trigger(args[0].id + '.' + event, args)
  forms.trigger(event, args)
}

function handleFormRequest (form, eventName, errors, data) {
  const timeStart = Date.now()
  const pageHeight = document.body.clientHeight

  // re-populate form if an error occurred
  if (errors) {
    form.setData(data)
  }

  // scroll to form
  if (window.scrollY <= 10 && submittedForm.auto_scroll) {
    scrollToElement(form.element)
  }

  // trigger events on window.load so all other scripts have loaded
  window.addEventListener('load', function () {
    trigger('submitted', [form])

    if (errors) {
      trigger('error', [form, errors])
    } else {
      // form was successfully submitted
      trigger('success', [form, data])

      // subscribed / unsubscribed
      trigger(eventName, [form, data])

      // for BC: always trigger "subscribed" event when firing "updated_subscriber" event
      if (eventName === 'updated_subscriber') {
        trigger('subscribed', [form, data, true])
      }
    }

    // scroll to form again if page height changed since last scroll, eg because of slow loading images
    // (only if load didn't take too long to prevent overtaking user scroll)
    const timeElapsed = Date.now() - timeStart
    if (submittedForm.auto_scroll && timeElapsed > 1000 && timeElapsed < 2000 && document.body.clientHeight !== pageHeight) {
      scrollToElement(form.element)
    }
  })
}

if (submittedForm) {
  const element = document.getElementById(submittedForm.element_id)
  const form = forms.getByElement(element)
  handleFormRequest(form, submittedForm.event, submittedForm.errors, submittedForm.data)
}
