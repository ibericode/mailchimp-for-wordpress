'use strict';

var notices = [];

function message(txt) {
    return '<div class="notice notice-warning"><p>' + txt + '</p></div>';
}

function show(txt) {
    var index = notices.indexOf(txt);
    if( index < 0 ) {
        notices.push(txt);
        render();
    }
}

function hide(txt) {
    var index = notices.indexOf(txt);
    if( index > -1 ) {
        delete notices[index];
        render();
    }
}

function render() {
    var html = '';
    for( var i=0; i < notices.length; i++) {
        html += message(notices[i]);
    }

    var container = document.querySelector('.mc4wp-notices');
    if( ! container ) {
        container = document.createElement('div');
        container.className = 'mc4wp-notices';
        var heading = document.querySelector('h1');
        heading.parentNode.insertBefore(container, heading.nextSibling);
    }

    container.innerHTML = html;
}

function init( editor ) {
    editor.on('change', function() {
        var text = "Your form contains old style <code>GROUPINGS</code> fields. <br /><br />Please remove these fields from your form and then re-add them through the available field buttons to make sure your data is getting through to MailChimp correctly.";
        editor.getValue().toLowerCase().indexOf('name="groupings') > -1 ? show(text) : hide(text);
    });
}

function groupingFields() {

}

module.exports = {
    "init": init
};