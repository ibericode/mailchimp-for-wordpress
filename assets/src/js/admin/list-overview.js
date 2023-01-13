const ajaxurl = window.mc4wp_vars.ajaxurl

function showDetails (evt) {
  evt.preventDefault()

  const link = evt.target
  const next = link.parentElement.parentElement.nextElementSibling
  const listID = link.getAttribute('data-list-id')
  const mount = next.querySelector('div')

  if (next.style.display === 'none') {
    const xhr = new XMLHttpRequest()
    xhr.open('GET', ajaxurl + '?action=mc4wp_get_list_details&format=html&ids=' + listID, true)
    xhr.onload = function () {
      if (this.status >= 400) {
        return
      }

      mount.innerHTML = this.responseText
    }
    xhr.send(null)

    next.style.display = ''
  } else {
    next.style.display = 'none'
  }
}

const table = document.getElementById('mc4wp-mailchimp-lists-overview')
if (table) {
  table.addEventListener('click', (evt) => {
    if (!evt.target.matches('.mc4wp-mailchimp-list')) {
      return
    }

    showDetails(evt)
  })
}
