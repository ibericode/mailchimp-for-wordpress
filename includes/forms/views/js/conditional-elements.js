/**
 * Conditional form elements for Mailchimp for WordPress.
 *
 * Shows or hides elements based on data-show-if / data-hide-if attributes.
 * Loaded inline only when a form uses conditional elements.
 *
 * @since 4.13.0
 */
(function () {
  function getFieldValues (form, fieldName) {
    var values = []
    var inputs = form.querySelectorAll('[name^="' + fieldName + '"]')

    for (var i = 0; i < inputs.length; i++) {
      if ((inputs[i].type === 'radio' || inputs[i].type === 'checkbox') && !inputs[i].checked) {
        continue
      }

      values.push(inputs[i].value)
    }

    return values
  }

  function findForm (element) {
    var bubbleElement = element

    while (bubbleElement.parentElement) {
      bubbleElement = bubbleElement.parentElement

      if (bubbleElement.tagName === 'FORM') {
        return bubbleElement
      }
    }

    return null
  }

  function toggleElement (el) {
    var show = !!el.getAttribute('data-show-if')
    var conditions = show ? el.getAttribute('data-show-if').split(':') : el.getAttribute('data-hide-if').split(':')
    var fieldName = conditions[0]
    var expectedValues = ((conditions.length > 1 ? conditions[1] : '*').split('|'))
    var form = findForm(el)

    if (!form) {
      return
    }

    var values = getFieldValues(form, fieldName)

    // determine whether condition is met
    var conditionMet = false
    for (var i = 0; i < values.length && !conditionMet; i++) {
      // condition is met when value is in array of expected values OR expected values contains a wildcard and value is not empty
      conditionMet = expectedValues.indexOf(values[i]) > -1 || (expectedValues.indexOf('*') > -1 && values[i].length > 0)
    }

    // derive single visible flag that works for both show and hide cases
    var visible = show ? conditionMet : !conditionMet

    // toggle element display
    el.style.display = visible ? '' : 'none'

    // find all inputs inside this element and toggle [required] attr (to prevent HTML5 validation on hidden elements)
    var inputs = el.querySelectorAll('input,select,textarea')
    for (var j = 0; j < inputs.length; j++) {
      if (visible && inputs[j].getAttribute('data-was-required')) {
        inputs[j].required = true
        inputs[j].removeAttribute('data-was-required')
      }

      if (!visible && inputs[j].required) {
        inputs[j].setAttribute('data-was-required', 'true')
        inputs[j].required = false
      }
    }
  }

  // evaluate conditional elements globally
  function evaluate () {
    var elements = document.querySelectorAll('.mc4wp-form [data-show-if],.mc4wp-form [data-hide-if]')
    for (var i = 0; i < elements.length; i++) {
      toggleElement(elements[i])
    }
  }

  // re-evaluate conditional elements for change events on forms
  function handleInputEvent (evt) {
    if (!evt.target || !evt.target.form || evt.target.form.className.indexOf('mc4wp-form') < 0) {
      return
    }

    var elements = evt.target.form.querySelectorAll('[data-show-if],[data-hide-if]')
    for (var i = 0; i < elements.length; i++) {
      toggleElement(elements[i])
    }
  }

  document.addEventListener('keyup', handleInputEvent, true)
  document.addEventListener('change', handleInputEvent, true)
  document.addEventListener('mc4wp-refresh', evaluate, true)
  window.addEventListener('load', evaluate)
  evaluate()
})();
