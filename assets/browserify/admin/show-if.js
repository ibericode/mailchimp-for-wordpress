const showIfElements = document.querySelectorAll('[data-showif]');

[].forEach.call(showIfElements, function (element) {
  const config = JSON.parse(element.getAttribute('data-showif'))
  const parentElements = document.querySelectorAll('[name="' + config.element + '"]')
  const inputs = element.querySelectorAll('input,select,textarea:not([readonly])')
  const hide = config.hide === undefined || config.hide

  function toggleElement () {
    // do nothing with unchecked radio inputs
    if (this.type === 'radio' && !this.checked) {
      return
    }

    const value = (this.type === 'checkbox') ? this.checked : this.value
    const conditionMet = (String(value) === String(config.value))

    if (hide) {
      element.style.display = conditionMet ? '' : 'none'
      element.style.visibility = conditionMet ? '' : 'hidden'
    } else {
      element.style.opacity = conditionMet ? '' : '0.4'
    }

    // disable input fields to stop sending their values to server
    [].forEach.call(inputs, function (inputElement) {
      inputElement.readOnly = !conditionMet
    })
  }

  // find checked element and call toggleElement function
  [].forEach.call(parentElements, function (el) {
    el.addEventListener('change', toggleElement)
    toggleElement.call(el)
  })
})
