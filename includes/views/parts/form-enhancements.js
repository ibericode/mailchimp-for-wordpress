(function() {
	function addSubmittedClassToFormContainer(e) {
		var form = e.target.form.parentNode;
		var className = 'mc4wp-form-submitted';
		(form.classList) ? form.classList.add(className) : form.className += ' ' + className;
	}

	function maybePrefixUrlField() {
		if(this.value.indexOf('http') !== 0) {
			this.value = "http://" + this.value;
		}
	}

	var forms = document.querySelectorAll('.mc4wp-form');
	for (var i = 0; i < forms.length; i++) {
		(function(f) {

			/* add class on submit */
			var b = f.querySelector('[type="submit"], [type="image"]');
			if(b.length > 0 ) {
				if(b.addEventListener) {
					b.addEventListener('click', addSubmittedClassToFormContainer);
				} else {
					b.attachEvent('click', addSubmittedClassToFormContainer);
				}
			}

			/* better URL fields */
			var urlFields = f.querySelectorAll('input[type="url"]');
			if( urlFields.length ) {
				for( var j=0; j < urlFields.length; j++ ) {
					if(urlFields[j].addEventListener) {
						urlFields[j].addEventListener('blur', maybePrefixUrlField);
					} else {
						urlFields[j].attachEvent( 'blur', maybePrefixUrlField);
					}
				}
			}

		})(forms[i]);
	}
})();

