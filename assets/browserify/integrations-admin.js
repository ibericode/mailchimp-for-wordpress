'use strict';

require('./admin/settings.js')
const events = require('./admin/events.js');
const notice = document.getElementById('notice-additional-fields');

function checkRequiredListFields( ) {
	const allowedFields = [ 'EMAIL' ];
	const ids = [].filter.call(document.querySelectorAll('.mc4wp-list-input'), i => i.checked).map(i => i.value).join(',');

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

