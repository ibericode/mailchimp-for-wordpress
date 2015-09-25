(function() {
	/* test if browser supports date fields */
	var testInput = document.createElement('input');
	testInput.setAttribute('type', 'date');
	if( testInput.type !== 'date') {

		/* add placeholder & pattern to all date fields */
		var dateFields = document.querySelectorAll('.mc4wp-form input[type="date"]');
		for(var i=0; i<dateFields.length; i++) {
			if(!dateFields[i].placeholder) {
				dateFields[i].placeholder = 'yyyy/mm/dd';
			}
			if(!dateFields[i].pattern) {
				dateFields[i].pattern = '(?:19|20)[0-9]{2}/(?:(?:0[1-9]|1[0-2])/(?:0[1-9]|1[0-9]|2[0-9])|(?:(?!02)(?:0[1-9]|1[0-2])/(?:30))|(?:(?:0[13578]|1[02])-31))';
			}
		}
	}
})();
