'use strict';

var settings = mc4wp.settings;
var events = mc4wp.events;
var notice = document.getElementById('notice-additional-fields');

function checkRequiredListFields( ) {

	var lists = settings.getSelectedLists();

	var showNotice = false;
	var allowedFields = [ 'EMAIL', 'FNAME', 'NAME', 'LNAME' ];

	loop:
	for( var i=0; i<lists.length; i++) {
		var list = lists[i];

		for( var j=0; j<list.merge_fields.length; j++) {
			var f = list.merge_fields[j];

			if(f.required && allowedFields.indexOf(f.tag) < 0) {
				showNotice = true;
				break loop;
			}
		}
	}

	notice.style.display = showNotice ? '' : 'none';
}

if( notice ) {
	checkRequiredListFields();
	events.on('selectedLists.change', checkRequiredListFields );
}

