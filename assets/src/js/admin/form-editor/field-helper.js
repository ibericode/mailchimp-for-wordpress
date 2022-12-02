const m = require('mithril')
const editor = require('./form-editor.js')
const fields = require('./fields.js')
const i18n = window.mc4wp_forms_i18n
const generate = require('./field-generator.js')
const Overlay = require('../overlay.js')
const forms = require('./field-forms.js')
let fieldConfig

editor.on('blur', () => m.redraw())

/**
 * Choose a field to open the helper form for
*/
function setActiveField (name) {
  fieldConfig = name !== null ? fields.get(name) : null

  // if this hidden field has choices (hidden groups), glue them together by their label.
  if (fieldConfig && fieldConfig.type === 'hidden' && fieldConfig.choices.length > 0) {
    fieldConfig.value = fieldConfig.choices.map(function (c) {
      return c.label
    }).join('|')
  }

  m.redraw()
}

/**
 * Create HTML based on current config object
 */
function createFieldHTMLAndAddToForm () {
  // generate html
  const html = generate(fieldConfig)

  // add to editor
  editor.insert(html)

  // reset field form
  setActiveField(null)
}

/**
 * View
 * @returns {*}
 */
function view () {
  // build DOM for fields choice
  const availableFields = fields.getAll()

  const fieldsChoice = m('div#mc4wp-available-fields.mc4wp-margin-s', [
    m('h4', { style: { marginTop: 0 } }, i18n.chooseField),

    [i18n.listFields, i18n.interestCategories, i18n.formFields].map(function (category) {
      const categoryFields = availableFields.filter(function (f) {
        return f.category === category
      })

      if (categoryFields.length === 0) {
        return ''
      }

      return m('div.mc4wp-margin-s', [
        m('h4', category),

        // render fields
        categoryFields.map(function (field) {
          let className = 'button'
          if (field.forceRequired) {
            className += ' is-required'
          }

          const inForm = field.inFormContent
          if (inForm !== null) {
            className += ' ' + (inForm ? 'in-form' : 'not-in-form')
          }

          return m('button', {
            className,
            type: 'button',
            onclick: (evt) => setActiveField(evt.target.value),
            value: field.name
          }, field.title)
        })
      ])
    })
  ])

  // build DOM for overlay
  let form = null
  if (fieldConfig) {
    form = m(Overlay, { onClose: () => setActiveField(null) }, // field wizard
      m('div#mc4wp-add-form-field', [
        // heading
        m('h3', [
          fieldConfig.title,
          fieldConfig.forceRequired ? m('span.mc4wp-red', '*') : '',
          fieldConfig.name.length ? m('code', fieldConfig.name) : ''
        ]),

        // help text
        (fieldConfig.help.length) ? m('p', m.trust(fieldConfig.help)) : '',

        // actual form
        forms.render(fieldConfig),

        // add to form button
        m('p', [
          m('button', {
            class: 'button-primary',
            type: 'button',
            onkeydown: function (evt) {
              if (evt.keyCode === 13) {
                createFieldHTMLAndAddToForm()
              }
            },
            onclick: createFieldHTMLAndAddToForm
          }, i18n.addToForm)
        ])
      ]))
  }

  return [
    fieldsChoice,
    form
  ]
}

const fieldHelperRootElement = document.getElementById('mc4wp-field-wizard')
if (fieldHelperRootElement) {
  m.mount(fieldHelperRootElement, { view })
}
