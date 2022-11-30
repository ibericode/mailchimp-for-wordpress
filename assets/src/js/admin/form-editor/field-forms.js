const forms = {}
const rows = require('./field-forms-rows.js')
const m = require('mithril')

// wrap row in div element with margin class
function wrap (rows) {
  for (let i = 0; i < rows.length; i++) {
    rows[i] = m('div.mc4wp-margin-s', rows[i])
  }
  return rows
}

// route to one of the other form configs, default to "text"
forms.render = function (config) {
  const type = config.type

  if (typeof (forms[type]) === 'function') {
    return wrap(forms[type](config))
  }

  if (['select', 'radio', 'checkbox'].indexOf(type) > -1) {
    return wrap(forms.choice(config))
  }

  // fallback to good old text field
  return wrap(forms.text(config))
}

forms.text = function (config) {
  return [
    rows.label(config),
    rows.placeholder(config),
    rows.value(config),
    rows.isRequired(config),
    rows.useParagraphs(config)
  ]
}

forms.choice = function (config) {
  const visibleRows = [
    rows.label(config),
    rows.choiceType(config),
    rows.choices(config)
  ]

  if (config.type === 'select') {
    visibleRows.push(rows.placeholder(config))
  }

  visibleRows.push(rows.useParagraphs(config))

  if (config.type === 'select' || config.type === 'radio') {
    visibleRows.push(rows.isRequired(config))
  }

  return visibleRows
}

forms.hidden = function (config) {
  config.placeholder = ''
  config.label = ''
  config.wrap = false

  return [
    rows.showType(config),
    rows.value(config)
  ]
}

forms.submit = function (config) {
  config.label = ''
  config.placeholder = ''

  return [
    rows.value(config),
    rows.useParagraphs(config)
  ]
}

forms['terms-checkbox'] = function (config) {
  return [
    rows.label(config),
    rows.linkToTerms(config),
    rows.isRequired(config),
    rows.useParagraphs(config)
  ]
}

forms.number = function (config) {
  return [
    forms.text(config),
    rows.numberMinMax(config)
  ]
}

module.exports = forms
