function validate (evt) {
  const node = document.createElement('p')
  node.className = 'help red'
  node.innerText = window.mc4wp_vars.i18n.invalid_api_key

  if (field.nextElementSibling.innerText === node.innerText) {
    field.nextElementSibling.parentElement.removeChild(field.nextElementSibling)
  }

  if (!field.value.match(/^[0-9a-zA-Z*]{32}-[a-z]{2}[0-9]{1,2}$/)) {
    field.parentElement.insertBefore(node, field.nextElementSibling)
  }
}

const field = document.getElementById('mailchimp_api_key')
if (field) {
  field.addEventListener('change', validate)
}
