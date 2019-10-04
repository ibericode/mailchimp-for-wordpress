'use strict';

const events = require('./admin/events.js');
const settings = require('./admin/settings.js');
const notice = document.getElementById('notice-additional-fields');

function checkRequiredListFields( ) {
	const lists = settings.getSelectedLists();
	const allowedFields = [ 'EMAIL' ];
	const ids = lists.map(l => l.id).join(',');

	//const allowedFields = [ 'EMAIL', 'FNAME', 'NAME', 'LNAME' ];
	let showNotice = false;

	window.fetch(`${ajaxurl}?action=mc4wp_get_list_details&ids=${ids}`)
		.then(r => r.json())
		.then(lists => {
			lists.forEach(list => {
				list.merge_fields.forEach(f => {
					if(f.required && allowedFields.indexOf(f.tag) < 0) {
						showNotice = true;
					}
				})
			});
		}).finally(() => {
			notice.style.display = showNotice ? '' : 'none';
		});
}

if( notice ) {
	checkRequiredListFields();
	events.on('selectedLists.change', checkRequiredListFields );
}

