const serialize = require('form-serialize')
const populate = require('populate.js')

/**
 * Creates a new Form object from the given ID and HTML element
 * @param {string} id
 * @param {Element} element
 * @constructor
 */
const Form = function (id, element) {
  this.id = id
  this.element = element || document.createElement('form')
  this.name = this.element.getAttribute('data-name') || 'Form #' + this.id
  this.errors = []
  this.started = false
}

/**
 * Sets the value for each field inside the form to the given data object
 * @param {object} data
 */
Form.prototype.setData = function (data) {
  try {
    populate(this.element, data)
  } catch (e) {
    console.error(e)
  }
}

/**
 * Returns the form values as a JSON object
 * @returns {object}
 */
Form.prototype.getData = function () {
  return serialize(this.element, { hash: true, empty: true })
}

/**
 * Returns the form values as a query param string
 * @returns {string}
 */
Form.prototype.getSerializedData = function () {
  return serialize(this.element, { hash: false, empty: true })
}

/**
 * Sets the HTML of the .mc4wp-response element inside this form to the provided msg param
 * @param {string} msg
 */
Form.prototype.setResponse = function (msg) {
  this.element.querySelector('.mc4wp-response').innerHTML = msg
}

/**
 * Reverts the form back to its initial state
 */
Form.prototype.reset = function () {
  this.setResponse('')
  this.element.querySelector('.mc4wp-form-fields').style.display = ''
  this.element.reset()
}

module.exports = Form
