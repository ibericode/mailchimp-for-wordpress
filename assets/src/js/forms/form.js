const serialize = require('form-serialize')
const populate = require('populate.js')

const Form = function (id, element) {
  this.id = id
  this.element = element || document.createElement('form')
  this.name = this.element.getAttribute('data-name') || 'Form #' + this.id
  this.errors = []
  this.started = false
}

Form.prototype.setData = function (data) {
  try {
    populate(this.element, data)
  } catch (e) {
    console.error(e)
  }
}

Form.prototype.getData = function () {
  return serialize(this.element, { hash: true, empty: true })
}

Form.prototype.getSerializedData = function () {
  return serialize(this.element, { hash: false, empty: true })
}

Form.prototype.setResponse = function (msg) {
  this.element.querySelector('.mc4wp-response').innerHTML = msg
}

// revert back to original state
Form.prototype.reset = function () {
  this.setResponse('')
  this.element.querySelector('.mc4wp-form-fields').style.display = ''
  this.element.reset()
}

module.exports = Form
