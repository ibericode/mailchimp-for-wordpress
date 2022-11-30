const context = document.getElementById('mc4wp-admin')
const listInputs = context.querySelectorAll('.mc4wp-list-input')
const lists = window.mc4wp_vars.mailchimp.lists
let selectedLists = []
const listeners = {}

function getSelectedLists () {
  return selectedLists
}

function updateSelectedLists () {
  selectedLists = []
  for (let i = 0; i < listInputs.length; i++) {
    const input = listInputs[i]

    // skip unchecked checkboxes
    if (typeof (input.checked) === 'boolean' && !input.checked) {
      continue
    }

    if (typeof (lists[input.value]) === 'object') {
      selectedLists.push(lists[input.value])
    }
  }

  toggleVisibleLists()
  emit('selectedLists.change', [selectedLists])
  return selectedLists
}

function toggleVisibleLists () {
  const rows = document.querySelectorAll('.lists--only-selected > *')
  for (let i = 0; i < rows.length; i++) {
    const listId = rows[i].getAttribute('data-list-id')
    const isSelected = selectedLists.filter(list => list.id === listId).length > 0
    rows[i].style.display = isSelected ? '' : 'none'
  }
}

function emit (event, args) {
  listeners[event] = listeners[event] || []
  for (let i = 0; i < listeners[event].length; i++) {
    listeners[event][i].apply(null, args)
  }
}

function on (event, func) {
  listeners[event] = listeners[event] || []
  listeners[event].push(func)
}

const listsWrapperEl = document.getElementById('mc4wp-lists')
if (listsWrapperEl) listsWrapperEl.addEventListener('change', updateSelectedLists)

updateSelectedLists()

module.exports = {
  getSelectedLists,
  on
}
