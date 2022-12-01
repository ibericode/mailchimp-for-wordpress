const m = require('mithril')
const fields = require('./fields.js')
const settings = window.mc4wp.settings
const ajaxurl = window.mc4wp_vars.ajaxurl
const i18n = window.mc4wp_forms_i18n
const mailchimp = window.mc4wp_vars.mailchimp
const countries = window.mc4wp_vars.countries
const registeredFields = []

/**
 * Reset all previously registered fields
 */
function reset () {
  // clear all of our fields
  registeredFields.forEach(fields.deregister)
  m.redraw()
}

/**
 * Helper function to quickly register a field and store it in local scope
 *
 * @param {string} category
 * @param {object} data
 * @param {boolean} sticky
 */
function register (category, data, sticky) {
  const field = fields.register(category, data)

  if (!sticky) {
    registeredFields.push(field)
  }
}

/**
 * Normalizes the field type which is passed by Mailchimp
 *
 * @param type
 * @returns {*}
 */
function getFieldType (type) {
  const map = {
    phone: 'tel',
    dropdown: 'select',
    checkboxes: 'checkbox',
    birthday: 'text'
  }

  return typeof map[type] !== 'undefined' ? map[type] : type
}

/**
 * Register the various fields for a merge var
 *
 * @param mergeField
 * @returns {boolean}
 */
function registerMergeField (mergeField) {
  const category = i18n.listFields
  const fieldType = getFieldType(mergeField.type)

  // name, type, title, value, required, label, placeholder, choices, wrap
  const data = {
    name: mergeField.tag,
    title: mergeField.name,
    required: mergeField.required,
    forceRequired: mergeField.required,
    type: fieldType,
    choices: mergeField.options.choices,
    acceptsMultipleValues: false // merge fields never accept multiple values.
  }

  if (data.type !== 'address') {
    register(category, data, false)
  } else {
    register(category, { name: data.name + '[addr1]', type: 'text', mailchimpType: 'address', title: i18n.streetAddress }, false)
    register(category, { name: data.name + '[city]', type: 'text', mailchimpType: 'address', title: i18n.city }, false)
    register(category, { name: data.name + '[state]', type: 'text', mailchimpType: 'address', title: i18n.state }, false)
    register(category, { name: data.name + '[zip]', type: 'text', mailchimpType: 'address', title: i18n.zip }, false)
    register(category, { name: data.name + '[country]', type: 'select', mailchimpType: 'address', title: i18n.country, choices: countries }, false)
  }

  return true
}

/**
 * Register a field for a Mailchimp grouping
 *
 * @param interestCategory
 */
function registerInterestCategory (interestCategory) {
  const fieldType = getFieldType(interestCategory.type)

  const data = {
    title: interestCategory.title,
    name: 'INTERESTS[' + interestCategory.id + ']',
    type: fieldType,
    choices: interestCategory.interests,
    acceptsMultipleValues: fieldType === 'checkbox'
  }
  register(i18n.interestCategories, data, false)
}

/**
 * Register all fields belonging to a list
 *
 * @param list
 */
function registerListFields (list) {
  // make sure EMAIL && public fields come first
  list.merge_fields = list.merge_fields.sort(function (a, b) {
    if (a.tag === 'EMAIL' || (a.public && !b.public)) {
      return -1
    }

    if (!a.public && b.public) {
      return 1
    }

    return 0
  })

  // loop through merge vars
  list.merge_fields.forEach(registerMergeField)

  // loop through groupings
  list.interest_categories.forEach(registerInterestCategory)

  m.redraw()
}

/**
 * Register all lists fields
 *
 * @param lists
 */
function registerListsFields (lists) {
  const url = ajaxurl + '?action=mc4wp_get_list_details&ids=' + lists.map(l => l.id).join(',')

  m.request({
    url,
    method: 'GET'
  }).then(lists => {
    reset()

    lists.forEach(registerListFields)
  })
}

function registerCustomFields (lists) {
  let choices

  register(i18n.listFields, {
    name: 'EMAIL',
    title: i18n.emailAddress,
    required: true,
    forceRequired: true,
    type: 'email'
  }, true)

  // register submit button
  register(i18n.formFields, {
    name: '',
    value: i18n.subscribe,
    type: 'submit',
    title: i18n.submitButton
  }, true)

  // register lists choice field
  choices = {}
  for (const key in lists) {
    choices[lists[key].id] = lists[key].name
  }

  register(i18n.formFields, {
    name: '_mc4wp_lists',
    type: 'checkbox',
    title: i18n.listChoice,
    choices,
    help: i18n.listChoiceDescription,
    acceptsMultipleValues: true
  }, true)

  choices = {
    subscribe: 'Subscribe',
    unsubscribe: 'Unsubscribe'
  }
  register(i18n.formFields, {
    name: '_mc4wp_action',
    type: 'radio',
    title: i18n.formAction,
    choices,
    value: 'subscribe',
    help: i18n.formActionDescription
  }, true)

  register(i18n.formFields, {
    name: 'AGREE_TO_TERMS',
    value: 1,
    type: 'terms-checkbox',
    label: i18n.agreeToTerms,
    title: i18n.agreeToTermsShort,
    showLabel: false,
    required: true
  }, true)
}

/**
 * Init
 */
settings.on('selectedLists.change', registerListsFields)

registerListsFields(settings.getSelectedLists())
registerCustomFields(mailchimp.lists)
