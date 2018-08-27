'use strict';

var $ = window.jQuery;
var config = pl4wp_vars;
var i18n = config.i18n;

function ListFetcher() {
    this.working = false;
    this.done = false;

    // start fetching right away when no lists but api key given
    if( config.phplist.api_connected && config.phplist.lists.length === 0 ) {
        this.fetch();
    }
}

ListFetcher.prototype.fetch = function (e) {
    e && e.preventDefault();

    this.working = true;
    this.done = false;

    $.post(ajaxurl, {
        action: "pl4wp_renew_phplist_lists",
		timeout: 180000,
    }).done(function(data) {
		this.success = true;

        if(data) {
            window.setTimeout(function() { window.location.reload(); }, 3000 );
        }
    }.bind(this)).fail(function(data) {
		this.success = false;
	}.bind(this)).always(function (data) {
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
                value: this.working ? i18n.fetching_phplist_lists : i18n.renew_phplist_lists,
                className: "button",
                disabled: !!this.working
            }),
            m.trust(' &nbsp; '),

            this.working ? [
                m('span.pl4wp-loader', "Loading..."),
                m.trust(' &nbsp; '),
                m('em.help', i18n.fetching_phplist_lists_can_take_a_while)
            ]: '',

            this.done ? [
                this.success ? m( 'em.help.green', i18n.fetching_phplist_lists_done ) : m('em.help.red', i18n.fetching_phplist_lists_error )
            ] : ''
        ])
    ]);
};

module.exports = ListFetcher;
