const ajaxurl = window.mc4wp_vars.ajaxurl
const m = require('mithril')

if (!Element.prototype.matches) {
  Element.prototype.matches = Element.prototype.msMatchesSelector ||
    Element.prototype.webkitMatchesSelector
}

function showDetails (evt) {
  evt.preventDefault()

  const link = evt.target
  const next = link.parentElement.parentElement.nextElementSibling
  const listID = link.getAttribute('data-list-id')
  const mount = next.querySelector('div')

  if (next.style.display === 'none') {
    m.request({
      method: 'GET',
      url: ajaxurl + '?action=mc4wp_get_list_details&ids=' + listID
    }).then(details => {
      m.render(mount, view(details.shift()))
    })

    next.style.display = ''
  } else {
    next.style.display = 'none'
  }
}

function view (data) {
  return [
    m('h3', 'Merge fields'),
    m('table.widefat.striped', [
      m('thead', [
        m('tr', [
          m('th', 'Name'),
          m('th', 'Tag'),
          m('th', 'Type')
        ])
      ]),
      m('tbody', data.merge_fields.map(f => (
        m('tr', [
          m('td', [
            f.name,
            f.required && m('span.mc4wp-red', '*')
          ]),
          m('td', [
            m('code', f.tag)
          ]),
          m('td', [
            f.type,
            ' ',
            f.options && f.options.date_format ? '(' + f.options.date_format + ')' : '',
            f.options && f.options.choices ? '(' + f.options.choices.join(', ') + ')' : ''
          ])
        ])
      )))
    ]),

    data.interest_categories.length > 0 && [
      m('h3', 'Interest Categories'),
      m('table.striped.widefat', [
        m('thead', [
          m('tr', [
            m('th', 'Name'),
            m('th', 'Type'),
            m('th', 'Interests')
          ])
        ]),
        m('tbody', data.interest_categories.map(f => (
          m('tr', [
            m('td', [
              m('strong', f.title),
              m('br'),
              m('br'),
              'ID: ',
              m('code', f.id)
            ]),
            m('td', f.type),
            m('td', [
              m('div.mc4wp-row', { style: 'margin-bottom: 4px;' }, [
                m('div.mc4wp-col.mc4wp-col-3', [
                  m('strong', { style: 'display: block; border-bottom: 1px solid #eee;' }, 'Name')
                ]),
                m('div.mc4wp-col.mc4wp-col-3', [
                  m('strong', { style: 'display: block; border-bottom: 1px solid #eee;' }, 'ID')
                ])
              ]),
              Object.keys(f.interests).map((id) => (
                m('div.mc4wp-row.mc4wp-margin-s', [
                  m('div.mc4wp-col.mc4wp-col-3', f.interests[id]),
                  m('div.mc4wp-col.mc4wp-col-3', [
                    m('code', { title: 'Interest ID' }, id)
                  ]),
                  m('br.clearfix.clear.cf')
                ])
              ))
            ])

          ])
        )))
      ])
    ]
  ]
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
