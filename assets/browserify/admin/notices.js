const editor = require('./form-editor/form-editor.js')
const fields = require('./form-editor/fields.js')
const settings = require('./settings')
const notices = {}

function show (id, text) {
  notices[id] = text
  render()
}

function hide (id) {
  delete notices[id]
  render()
}

function render () {
  let html = ''
  for (const key in notices) {
    html += '<div class="notice notice-warning inline"><p>' + notices[key] + '</p></div>'
  }

  let container = document.querySelector('.mc4wp-notices')
  if (!container) {
    container = document.createElement('div')
    container.className = 'mc4wp-notices'
    const heading = document.querySelector('h1, h2')
    heading.parentNode.insertBefore(container, heading.nextSibling)
  }

  container.innerHTML = html
}

const groupingsNotice = function () {
  const text = 'Your form contains deprecated <code>GROUPINGS</code> fields. <br /><br />Please remove these fields from your form and then re-add them through the available field buttons to make sure your data is getting through to Mailchimp correctly.'
  const formCode = editor.getValue().toLowerCase();
  (formCode.indexOf('name="groupings') > -1) ? show('deprecated_groupings', text) : hide('deprecated_groupings')
}

const requiredFieldsNotice = function () {
  const requiredFields = fields.getAllWhere('forceRequired', true)
  const missingFields = requiredFields.filter(function (f) {
    return !editor.containsField(f.name.toUpperCase())
  })

  let text = '<strong>Heads up!</strong> Your form is missing list fields that are required in Mailchimp. Either add these fields to your form or mark them as optional in Mailchimp.'
  text += '<br /><ul class="ul-square" style="margin-bottom: 0;"><li>' + missingFields.map(function (f) { return f.title }).join('</li><li>') + '</li></ul>';

  (missingFields.length > 0) ? show('required_fields_missing', text) : hide('required_fields_missing')
}

const mailchimpListsNotice = function () {
  const text = '<strong>Heads up!</strong> You have not yet selected a Mailchimp list to subscribe people to. Please select at least one list from the <a href="javascript:void(0)" data-tab="settings" class="tab-link">settings tab</a>.'

  if (settings.getSelectedLists().length > 0) {
    hide('no_lists_selected')
  } else {
    show('no_lists_selected', text)
  }
}

// old groupings
groupingsNotice()
editor.on('focus', groupingsNotice)
editor.on('blur', groupingsNotice)

// missing required fields
requiredFieldsNotice()
editor.on('blur', requiredFieldsNotice)
editor.on('focus', requiredFieldsNotice)

document.body.addEventListener('change', mailchimpListsNotice)
