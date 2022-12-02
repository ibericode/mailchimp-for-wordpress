const EventEmitter = require('../../events.js')
const events = new EventEmitter()
const fields = {}

function Field (data) {
  return {
    name: data.name,
    title: data.title || data.name,
    type: data.type,
    mailchimpType: data.mailchimpType || null,
    label: data.label || data.title || '',
    showLabel: typeof (data.showLabel) === 'boolean' ? data.showLabel : true,
    value: data.value || '',
    placeholder: data.placeholder || '',
    required: typeof (data.required) === 'boolean' ? data.required : false,
    forceRequired: typeof (data.forceRequired) === 'boolean' ? data.forceRequired : false,
    wrap: typeof (data.wrap) === 'boolean' ? data.wrap : true,
    min: data.min,
    max: data.max,
    help: data.help || '',
    choices: data.choices || [],
    inFormContent: null,
    acceptsMultipleValues: data.acceptsMultipleValues,
    link: data.link || ''
  }
}

function FieldChoice (data) {
  return {
    title: data.title || data.label,
    selected: data.selected || false,
    value: data.value || data.label,
    label: data.label
  }
}

function createChoices (data) {
  return Object.keys(data).map((key) => new FieldChoice({ label: data[key], value: key }))
}

function register (category, data) {
  // if a field with the exact same name already exists,
  // update its forceRequired property
  const existingField = fields[data.name]
  if (existingField) {
    if (!existingField.forceRequired && data.forceRequired) {
      existingField.forceRequired = true
    }

    return existingField
  }

  // array of choices given? convert to FieldChoice objects
  if (data.choices) {
    data.choices = createChoices(data.choices)

    if (data.value) {
      data.choices = data.choices.map(function (choice) {
        if (choice.value === data.value) {
          choice.selected = true
        }
        return choice
      })
    }
  }

  // create Field object
  const field = new Field(data)
  field.category = category

  // add to array
  fields[data.name] = field

  // trigger event
  events.emit('change', [])
  return field
}

function deregister (field) {
  delete fields[field.name]
}

function get (name) {
  return fields[name]
}

function getAll () {
  return Object.values(fields)
}

module.exports = {
  get,
  getAll,
  deregister,
  register,
  on: events.on.bind(events)
}
