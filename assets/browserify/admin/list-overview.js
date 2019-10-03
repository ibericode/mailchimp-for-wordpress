'use strict';

const m = require('mithril');
const $ = window.jQuery;

function showDetails(evt) {
    evt.preventDefault();

    $(this).parents('tr').next().toggle();
    let listID = this.getAttribute('data-list-id');
    let mount = $(this).parents('tr').next().find('div').get(0);

    m.request({
        method: "GET",
        url: ajaxurl + "?action=mc4wp_get_list_details&ids=" + listID,
    }).then(details => {
        m.render(mount, view(details[0]));
    })
}

function view(data) {
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
                        f.required && m('span.red', '*')
                    ]),
                    m('td', [
                        m('code', f.tag)
                    ]),
                    m('td', [
                        f.type,
                        ' ',
                        f.options && f.options.date_format ? '(' + f.options.date_format + ')' : '',
                        f.options && f.options.choices ? '(' + f.options.choices.join(', ') + ')' : '',
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
                        m('th', 'Interests'),
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
                            m('div.row', { style: 'margin-bottom: 4px;'}, [
                                m('div.col.col-3', [
                                    m('strong', {style: 'display: block; border-bottom: 1px solid #eee;'}, 'Name'),
                                ]),
                                m('div.col.col-3', [
                                    m('strong', {style: 'display: block; border-bottom: 1px solid #eee;'}, 'ID'),
                                ])
                            ]),
                            Object.keys(f.interests).map((id) => (
                                m('div.row.tiny-margin', [
                                    m('div.col.col-3', f.interests[id]),
                                    m('div.col.col-3', [
                                        m('code', {title: 'Interest ID'}, id)
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

$('#mc4wp-mailchimp-lists-overview .mc4wp-mailchimp-list').click(showDetails);

