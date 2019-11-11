function maybePrefixUrlField() {
	if (this.value.trim() !== '' && this.value.indexOf( 'http' ) !== 0) {
		this.value = "http://" + this.value;
	}
}

var urlFields = document.querySelectorAll( '.mc4wp-form input[type="url"]' );
if ( urlFields && urlFields.length > 0 ) {
	for ( var j = 0; j < urlFields.length; j++ ) {
		addEventListener( urlFields[j],'blur',maybePrefixUrlField );
	}
}
