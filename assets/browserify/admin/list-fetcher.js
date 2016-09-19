'use strict';

var $ = window.jQuery;

function ListFetcher() {
    this.working = false;
}

ListFetcher.prototype.fetch = function (e) {
    e.preventDefault();

    this.working = true;
    this.done = false;

    $.post(ajaxurl, {
        action: "mc4wp_renew_mailchimp_lists"
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
                value: "Renew MailChimp lists",
                className: "button",
                disabled: !!this.working
            }),
            m.trust(' &nbsp; '),

            this.working ? [
                m('div.loader', "Loading..."),
                m.trust(' &nbsp; '),
                m('em.help', "This can take a while if you have many MailChimp lists.")
            ]: '',

            this.done ? [
                m( 'em.help.green', "Done! MailChimp lists renewed.")
            ] : ''
        ])
    ]);
};

module.exports = new ListFetcher;