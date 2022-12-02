const htmlutil = require('html')
const m = require('mithril')

const setAttributes = function (vnode) {
  if (vnode.dom.checked) {
    vnode.dom.setAttribute('checked', 'true')
  }

  if (vnode.dom.value) {
    vnode.dom.setAttribute('value', vnode.dom.value)
  }

  if (vnode.dom.selected) {
    vnode.dom.setAttribute('selected', 'true')
  }
}

const generators = {}
/**
 * Generates a <select> field
 * @param config
 * @returns {*}
 */
generators.select = function (config) {
  const attributes = {
    name: config.name,
    required: config.required
  }
  let hasSelection = false

  const options = config.choices.map(function (choice) {
    if (choice.selected) {
      hasSelection = true
    }

    return m('option', {
      value: (choice.value !== choice.label) ? choice.value : undefined,
      selected: choice.selected,
      oncreate: setAttributes
    }, choice.label)
  })

  const placeholder = config.placeholder
  if (placeholder.length > 0) {
    options.unshift(
      m('option', {
        disabled: true,
        value: '',
        selected: !hasSelection,
        oncreate: setAttributes
      }, placeholder)
    )
  }

  return m('select', attributes, options)
}

generators['terms-checkbox'] = function (config) {
  let label

  if (config.link.length > 0) {
    label = m('a', { href: config.link, target: '_blank' }, config.label)
  } else {
    label = config.label
  }

  return m('label', [
    m('input', {
      name: config.name,
      type: 'checkbox',
      value: config.value,
      required: config.required
    }),
    ' ',
    label
  ])
}

/**
 * Generates a checkbox or radio type input field.
 *
 * @param config
 * @returns {*}
 */
generators.checkbox = function (config) {
  return config.choices.map(function (choice) {
    const name = config.name + (config.type === 'checkbox' ? '[]' : '')
    const required = config.required && config.type === 'radio'

    return m('label', [
      m('input', {
        name,
        type: config.type,
        value: choice.value,
        checked: choice.selected,
        required,
        oncreate: setAttributes
      }),
      ' ',
      m('span', choice.label)
    ]
    )
  })
}
generators.radio = generators.checkbox

/**
 * Generates a default field
 *
 * - text, url, number, email, date
 *
 * @param config
 * @returns {*}
 */
generators.default = function (config) {
  const attributes = {
    type: config.type
  }

  if (config.name) {
    attributes.name = config.name
  }

  if (config.min) {
    attributes.min = config.min
  }

  if (config.max) {
    attributes.max = config.max
  }

  if (config.value.length > 0) {
    attributes.value = config.value
  }

  if (config.placeholder.length > 0) {
    attributes.placeholder = config.placeholder
  }

  attributes.required = config.required
  attributes.oncreate = setAttributes

  return m('input', attributes)
}

/**
 * Generates an HTML string based on a field (config) object
 *
 * @param {object} config
 * @returns {string}
 */
function generate (config) {
  const labelAtts = {}
  const label = config.label.length > 0 && config.showLabel ? m('label', labelAtts, config.label) : ''
  const field = typeof (generators[config.type]) === 'function' ? generators[config.type](config) : generators.default(config)
  const htmlTemplate = config.wrap ? m('p', [label, field]) : [label, field]

  // render in vdom
  const vdom = document.createElement('div')
  m.render(vdom, htmlTemplate)

  // prettify html
  const html = htmlutil.prettyPrint(vdom.innerHTML)

  return html + '\n'
}

module.exports = generate
