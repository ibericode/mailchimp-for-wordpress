const i18n = window.mc4wp_forms_i18n
const m = require('mithril')
const r = {}

r.showType = function (config) {
  let fieldType = config.type
  fieldType = fieldType.charAt(0).toUpperCase() + fieldType.slice(1)

  return m('div', [
    m('label', i18n.fieldType),
    m('span', fieldType)
  ])
}

r.label = function (config) {
  // label row
  return m('div', [
    m('label', i18n.fieldLabel),
    m('input.widefat', {
      type: 'text',
      value: config.label,
      onchange: (evt) => {
        config.label = evt.target.value
      },
      placeholder: config.title
    })
  ])
}

r.value = function (config) {
  const isHidden = config.type === 'hidden'
  return m('div', [
    m('label', [
      isHidden ? i18n.value : i18n.initialValue,
      ' ',
      isHidden ? '' : m('small', { style: 'float: right; font-weight: normal;' }, i18n.optional)
    ]),
    m('input.widefat', {
      type: 'text',
      value: config.value,
      onchange: (evt) => {
        config.value = evt.target.value
      }
    }),
    isHidden ? '' : m('p.description', i18n.valueHelp)
  ])
}

r.numberMinMax = function (config) {
  return m('div.mc4wp-row', [
    m('div.mc4wp-col.mc4wp-col-3', [
      m('label', i18n.min),
      m('input', {
        type: 'number',
        onchange: (evt) => {
          config.min = evt.target.value
        }
      })
    ]),
    m('div.mc4wp-col.mc4wp-col-3', [
      m('label', i18n.max),
      m('input', {
        type: 'number',
        onchange: (evt) => {
          config.max = evt.target.value
        }
      })
    ])
  ])
}

r.isRequired = function (config) {
  const inputAtts = {
    type: 'checkbox',
    checked: config.required,
    onchange: (evt) => {
      config.required = evt.target.checked
    }
  }
  let desc

  if (config.forceRequired) {
    inputAtts.required = true
    inputAtts.disabled = true
    desc = m('p.description', i18n.forceRequired)
  }

  return m('div', [
    m('label.cb-wrap', [
      m('input', inputAtts),
      i18n.isFieldRequired
    ]),
    desc
  ])
}

r.placeholder = function (config) {
  return m('div', [
    m('label', [
      i18n.placeholder,
      ' ',
      m('small', { style: 'float: right; font-weight: normal;' }, i18n.optional)
    ]),
    m('input.widefat', {
      type: 'text',
      value: config.placeholder,
      onchange: (evt) => {
        config.placeholder = evt.target.value
      },
      placeholder: ''
    }),
    m('p.description', i18n.placeholderHelp)
  ])
}

r.useParagraphs = function (config) {
  return m('div', [
    m('label.cb-wrap', [
      m('input', {
        type: 'checkbox',
        checked: config.wrap,
        onchange: (evt) => {
          config.wrap = evt.target.checked
        }
      }),
      i18n.wrapInParagraphTags
    ])
  ])
}

r.choiceType = function (config) {
  const options = [
    m('option', {
      value: 'select',
      selected: config.type === 'select' ? 'selected' : false
    }, i18n.dropdown),
    m('option', {
      value: 'radio',
      selected: config.type === 'radio' ? 'selected' : false
    }, i18n.radioButtons)
  ]

  // only add checkbox choice if field accepts multiple values
  if (config.acceptsMultipleValues) {
    options.push(
      m('option', {
        value: 'checkbox',
        selected: config.type === 'checkbox' ? 'selected' : false
      }, i18n.checkboxes)
    )
  }

  return m('div', [
    m('label', i18n.choiceType),
    m('select', {
      value: config.type,
      onchange: (evt) => {
        config.type = evt.target.value
      }
    }, options)
  ])
}

r.choices = function (config) {
  const html = []
  html.push(m('div', [
    m('label', i18n.choices),
    m('div.limit-height', [
      m('table', config.choices.map(function (choice, index) {
        return m('tr', {
          'data-id': index
        }, [
          m('td.cb', m('input', {
            name: 'selected',
            type: (config.type === 'checkbox') ? 'checkbox' : 'radio',
            onchange: (evt) => {
              config.choices = config.choices.map((choice) => {
                if (choice.value === evt.target.value) {
                  choice.selected = !choice.selected
                } else {
                  // only checkboxes allow for multiple selections
                  if (config.type !== 'checkbox') {
                    choice.selected = false
                  }
                }

                return choice
              })
            },
            checked: choice.selected,
            value: choice.value,
            title: i18n.preselect
          })
          ),
          m('td.stretch', m('input.widefat', {
            type: 'text',
            value: choice.label,
            placeholder: choice.title,
            onchange: (evt) => {
              choice.label = evt.target.value
            }
          })),
          m('td', m('span', {
            title: i18n.remove,
            class: 'dashicons dashicons-no-alt hover-activated',
            onclick: function (key) {
              this.choices.splice(key, 1)
            }.bind(config, index)
          }, ''))
        ])
      })
      ) // end of table
    ]) // end of limit-height div
  ]))

  return html
}

r.linkToTerms = function (config) {
  // label row
  return m('div', [
    m('label', i18n.agreeToTermsLink),
    m('input.widefat', {
      type: 'text',
      value: config.link,
      onchange: (evt) => {
        config.link = evt.target.value
      },
      placeholder: 'https://...'
    })
  ])
}

module.exports = r
