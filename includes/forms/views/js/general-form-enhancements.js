function bindEvent(element, event, handler) {
	if(element.addEventListener) {
		element.addEventListener(event,handler);
	} else {
		element.attachEvent('on'+event,handler);
	}
}

function addSubmittedClassToFormContainer(e) {
	var form = e.target.form.parentNode;
	var className = 'mc4wp-form-submitted';
	(form.classList) ? form.classList.add(className) : form.className += ' ' + className;
}


var forms = document.querySelectorAll('.mc4wp-form');
for(var i = 0; i < forms.length; i++) {
	(function(f) {

		/* add class on submit */
		var b = f.querySelector('[type="submit"], [type="image"]');
		if( b ) {
			bindEvent(b,'click',addSubmittedClassToFormContainer);
		}

	})(forms[i]);
}


