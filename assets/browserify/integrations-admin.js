'use strict';

const settings = require('./admin/settings.js');
const notice = document.getElementById('notice-additional-fields');
require('./admin/show-if.js');

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

	settings.on('selectedLists.change', checkRequiredListFields );
}

