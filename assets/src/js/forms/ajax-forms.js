const forms = require('./forms.js')
const Loader = require('./ajax-form-loader.js')

// Do not activate if Premium's AJAX module has already initialized via mc4wp_ajax_vars.
// This is a secondary guard: the PHP class_exists('MC4WP_AJAX_Forms') check in
// class-asset-manager.php is the primary guard and prevents mc4wp.ajax from being
// localized when Premium is active. This JS check handles any edge case where
// plugin load order causes both to be present simultaneously.
const ajaxConfig = window.mc4wp && window.mc4wp.ajax
if (ajaxConfig && !(window.mc4wp_ajax_vars && window.mc4wp_ajax_vars.inited)) {
  let busy = false

  /**
   * Handle AJAX response data and update the form accordingly.
   *
   * @param {object} form  The mc4wp Form object
   * @param {object} response  Parsed JSON response from REST API
   */
  function handleResponseData (form, response) {
    forms.trigger('submitted', [form, null])

    if (response.error) {
      form.setResponse(response.error.message)
      forms.trigger('error', [form, response.error.errors])
    } else if (response.code && response.message) {
      form.setResponse(`<div class="mc4wp-alert mc4wp-error"><p>${response.message}</p></div>`)
      forms.trigger('error', [form, [response.code]])
    } else {
      const data = form.getData()

      forms.trigger('success', [form, data])
      forms.trigger(response.data.event, [form, data])

      // for BC: always trigger "subscribed" event when firing "updated_subscriber" event
      if (response.data.event === 'updated_subscriber') {
        forms.trigger('subscribed', [form, data, true])
      }

      if (response.data.hide_fields) {
        form.element.querySelector('.mc4wp-form-fields').style.display = 'none'
      }

      form.setResponse(response.data.message)
      form.element.reset()

      if (response.data.redirect_to) {
        window.location.href = response.data.redirect_to
      }
    }
  }

  /**
   * Submits the given form over AJAX using the REST API endpoint.
   *
   * @param {object} form  The mc4wp Form object
   */
  function ajaxSubmit (form) {
    if (busy) {
      return
    }

    const loader = new Loader(form.element, ajaxConfig.loading_character)
    loader.start()

    form.setResponse('')
    busy = true

    const request = new XMLHttpRequest()
    request.onreadystatechange = function () {
      if (request.readyState >= XMLHttpRequest.DONE) {
        loader.stop()
        busy = false

        if (request.status >= 200 && request.status < 500) {
          try {
            const data = JSON.parse(request.responseText)
            handleResponseData(form, data)
          } catch (e) {
            // eslint-disable-next-line no-console
            console.error(`Mailchimp for WordPress: failed to parse response: "${e}"`)
            form.setResponse(`<div class="mc4wp-alert mc4wp-error"><p>${ajaxConfig.error_text}</p></div>`)
          }
        } else {
          // eslint-disable-next-line no-console
          console.error(`Mailchimp for WordPress: request error: "${request.responseText}"`)
        }
      }
    }
    request.open('POST', ajaxConfig.ajax_url, true)
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded')
    request.setRequestHeader('Accept', 'application/json')
    request.send(form.getSerializedData())
  }

  /**
   * Intercepts form submissions for AJAX-enabled forms.
   *
   * @param {object} form  The mc4wp Form object
   * @param {Event} evt    The original submit event
   */
  function maybeSubmitOverAjax (form, evt) {
    if (form.element.getAttribute('class').indexOf('mc4wp-ajax') < 0) {
      return
    }

    if (document.activeElement && document.activeElement.tagName === 'INPUT') {
      document.activeElement.blur()
    }

    try {
      ajaxSubmit(form)
    } catch (e) {
      // eslint-disable-next-line no-console
      console.error(e)
      return
    }

    evt.preventDefault()
  }

  forms.on('submit', maybeSubmitOverAjax)
}
