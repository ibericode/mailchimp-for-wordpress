/* test if browser supports date fields */
var testInput = document.createElement( 'input' );
testInput.setAttribute( 'type', 'date' );
if ( testInput.type !== 'date') {

	/* add placeholder & pattern to all date fields */
	var dateFields = document.querySelectorAll( '.mc4wp-form input[type="date"]' );
	for (var i = 0; i < dateFields.length; i++) {
		if ( ! dateFields[i].placeholder) {
			dateFields[i].placeholder = 'YYYY-MM-DD';
		}
		if ( ! dateFields[i].pattern) {
			dateFields[i].pattern = '[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])';
		}
	}
}
