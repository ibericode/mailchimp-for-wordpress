'use strict';

var $ = window.jQuery;
var config = mc4wp_vars;
var i18n = config.i18n;

function ListFetcher() {
    this.working = false;
    this.done = false;

    // start fetching right away when no lists but api key given
    if( config.mailchimp.api_connected && config.mailchimp.lists.length == 0 ) {
        this.fetch();
    }
}

ListFetcher.prototype.fetch = function (e) {
    e && e.preventDefault();

    this.working = true;
    this.done = false;

    $.post(ajaxurl, {
        action: "mc4wp_renew_mailchimp_lists"
    }).done(function(data) {
        if(data) {
            window.setTimeout(function() { window.location.reload(); }, 3000 );
        }
    }).always(function (data) {
        this.working = false;
        this.done = true;

        m.redraw();
    }.bind(this));
};

ListFetcher.prototype.view = function () {
    return m('form', {
        method: "POST",
        onsubmit: this.fetch.bind(this)
    }, [
        m('p', [
            m('input', {
                type: "submit",
                value: this.working ? i18n.fetching_mailchimp_lists : i18n.renew_mailchimp_lists,
                className: "button",
                disabled: !!this.working
            }),
            m.trust(' &nbsp; '),

            this.working ? [
                m('span.mc4wp-loader', "Loading..."),
                m.trust(' &nbsp; '),
                m('em.help', i18n.fetching_mailchimp_lists_can_take_a_while)
            ]: '',

            this.done ? [
                m( 'em.help.green', i18n.fetching_mailchimp_lists_done )
            ] : ''
        ])
    ]);
};

module.exports = ListFetcher;