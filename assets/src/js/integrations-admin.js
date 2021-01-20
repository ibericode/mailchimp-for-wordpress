const ajaxurl = window.mc4wp_vars.ajaxurl
const settings = window.mc4wp.settings
const notice = document.getElementById('notice-additional-fields')

function checkRequiredListFields () {
  const ids = [].filter.call(document.querySelectorAll('.mc4wp-list-input'), i => i.checked).map(i => i.value).join(',')
  const allowedFields = ['EMAIL', 'FNAME', 'NAME', 'LNAME']
  let showNotice = false

  window.fetch(`${ajaxurl}?action=mc4wp_get_list_details&ids=${ids}`)
    .then(r => r.json())
    .then(lists => {
      lists.forEach(list => {
        list.merge_fields.forEach(f => {
          if (f.required && allowedFields.indexOf(f.tag) < 0) {
            showNotice = true
          }
        })
      })
    }).finally(() => {
      notice.style.display = showNotice ? '' : 'none'
    })
}

if (notice) {
  checkRequiredListFields()

  settings.on('selectedLists.change', checkRequiredListFields)
}
