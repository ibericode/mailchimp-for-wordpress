function getFieldValues (form, fieldName) {
  const values = []
  const inputs = form.querySelectorAll('input[name="' + fieldName + '"],select[name="' + fieldName + '"],textarea[name="' + fieldName + '"]')

  for (let i = 0; i < inputs.length; i++) {
    if ((inputs[i].type === 'radio' || inputs[i].type === 'checkbox') && !inputs[i].checked) {
      continue
    }

    values.push(inputs[i].value)
  }

  return values
}

function findForm (element) {
  let bubbleElement = element

  while (bubbleElement.parentElement) {
    bubbleElement = bubbleElement.parentElement

    if (bubbleElement.tagName === 'FORM') {
      return bubbleElement
    }
  }

  return null
}

function toggleElement (el) {
  const show = !!el.getAttribute('data-show-if')
  const conditions = show ? el.getAttribute('data-show-if').split(':') : el.getAttribute('data-hide-if').split(':')
  const fieldName = conditions[0]
  const expectedValues = ((conditions.length > 1 ? conditions[1] : '*').split('|'))
  const form = findForm(el)
  const values = getFieldValues(form, fieldName)

  // determine whether condition is met
  let conditionMet = false
  for (let i = 0; i < values.length && !conditionMet; i++) {
    // condition is met when value is in array of expected values OR expected values contains a wildcard and value is not empty
    conditionMet = expectedValues.indexOf(values[i]) > -1 || (expectedValues.indexOf('*') > -1 && values[i].length > 0)
  }

  // toggle element display
  if (show) {
    el.style.display = conditionMet ? '' : 'none'
  } else {
    el.style.display = conditionMet ? 'none' : ''
  }

  // find all inputs inside this element and toggle [required] attr (to prevent HTML5 validation on hidden elements)
  const inputs = el.querySelectorAll('input,select,textarea')
  for (let i = 0; i < inputs.length; i++) {
    if ((conditionMet || show) && inputs[i].getAttribute('data-was-required')) {
      inputs[i].required = true
      inputs[i].removeAttribute('data-was-required')
    }

    if ((!conditionMet || !show) && inputs[i].required) {
      inputs[i].setAttribute('data-was-required', 'true')
      inputs[i].required = false
    }
  }
}

// evaluate conditional elements globally
function evaluate () {
  const elements = document.querySelectorAll('.mc4wp-form [data-show-if],.mc4wp-form [data-hide-if]')
  for (let i = 0; i < elements.length; i++) {
    toggleElement(elements[i])
  }
}

// re-evaluate conditional elements for change events on forms
function handleInputEvent (evt) {
  if (!evt.target || !evt.target.form || evt.target.form.className.indexOf('mc4wp-form') < 0) {
    return
  }

  const elements = evt.target.form.querySelectorAll('[data-show-if],[data-hide-if]')
  for (let i = 0; i < elements.length; i++) {
    toggleElement(elements[i])
  }
}

document.addEventListener('keyup', handleInputEvent, true)
document.addEventListener('change', handleInputEvent, true)
document.addEventListener('mc4wp-refresh', evaluate, true)
window.addEventListener('load', evaluate)
evaluate()
