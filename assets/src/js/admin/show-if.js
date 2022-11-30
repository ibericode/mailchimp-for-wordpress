
function toggleElement (el, hide, input, condition) {
  // do nothing with unchecked radio inputs
  if (input.type === 'radio' && !input.checked) {
    return
  }

  const value = (input.type === 'checkbox') ? input.checked : input.value
  const conditionMet = (String(value) === String(condition))

  if (hide) {
    el.style.display = conditionMet ? '' : 'none'
    el.style.visibility = conditionMet ? '' : 'hidden'
  } else {
    el.style.opacity = conditionMet ? '' : '0.4'
  }

  // disable input fields inside this element to stop sending their values to server
  [].forEach.call(el.querySelectorAll('input,select,textarea:not([readonly])'), function (inputElement) {
    inputElement.readOnly = !conditionMet
  })
}

function init (el) {
  const config = JSON.parse(el.getAttribute('data-showif'))
  const parentElements = document.querySelectorAll('[name="' + config.element + '"]')
  const hide = config.hide === undefined || config.hide

  // find checked element and call toggleElement function
  for (let i = 0; i < parentElements.length; i++) {
    parentElements[i].addEventListener('change', toggleElement.bind(null, el, hide, parentElements[i], config.value))
    toggleElement(el, hide, parentElements[i], config.value)
  }
}

[].forEach.call(document.querySelectorAll('[data-showif]'), init)
