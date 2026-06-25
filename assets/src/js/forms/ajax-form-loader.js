/**
 * @param {HTMLInputElement} button
 * @returns {string}
 */
function getButtonText (button) {
  return button.innerHTML ? button.innerHTML : button.value
}

/**
 * @param {HTMLInputElement} button
 * @param {string} text
 */
function setButtonText (button, text) {
  if (button.innerHTML) {
    button.innerHTML = text
  } else {
    button.value = text
  }
}

/**
 * Constructs a new loader, which manipulates the form's button to show a loading indicator.
 *
 * @param {HTMLFormElement} formEl
 * @param {string} char
 * @constructor
 */
function Loader (formEl, char) {
  this.formEl = formEl
  this.button = formEl.querySelector('input[type="submit"], button[type="submit"]')
  this.char = char ?? '\u00B7'
  if (this.button) {
    this.originalButton = this.button.cloneNode(true)
  }
}

/**
 * Starts the loading indicator
 */
Loader.prototype.start = function () {
  const { button, formEl, char } = this
  if (button) {
    const loadingText = this.button.getAttribute('data-loading-text')
    if (loadingText) {
      setButtonText(button, loadingText)
    } else {
      button.style.width = window.getComputedStyle(this.button).width
      setButtonText(button, char)
      this.loadingInterval = window.setInterval(this.tick.bind(this), 500)
    }
  } else {
    formEl.style.opacity = '0.5'
  }

  formEl.className += ' mc4wp-loading'
}

/**
 * Stops the loading indicator
 */
Loader.prototype.stop = function () {
  const { button, originalButton, formEl, loadingInterval } = this
  if (this.button) {
    button.style.width = originalButton.style.width
    const text = getButtonText(originalButton)
    setButtonText(button, text)
    window.clearInterval(loadingInterval)
  } else {
    formEl.style.opacity = ''
  }

  formEl.className = formEl.className.replace('mc4wp-loading', '')
}

/**
 * Represents a single step in the loading indicator
 */
Loader.prototype.tick = function () {
  const { button, char } = this
  const text = getButtonText(button)
  setButtonText(button, text.length >= 5 ? char : `${text} ${char}`)
}

module.exports = Loader
