var FormWatcher = function(editor) {

	var $ = window.jQuery;

	// @todo fill this dynamically (get from selected lists)
	var requiredFields = [ { tag: 'EMAIL', name: 'Email Address' } ];
	var $missingFieldsList = $(document.getElementById('missing-fields-list'));
	var $missingFieldsNotice = $(document.getElementById('missing-fields-notice'));

	// functions
	function checkRequiredFields() {

		var formContent = editor.getValue();

		// let's go
		formContent = formContent.toLowerCase();

		// check presence of reach required field
		var missingFields = {};
		for(var i=0; i<requiredFields.length; i++) {
			var htmlString = 'name="' + requiredFields[i].tag.toLowerCase();
			if( formContent.indexOf( htmlString ) == -1 ) {
				missingFields[requiredFields[i].tag] = requiredFields[i];
			}
		}

		// do nothing if no fields are missing
		if($.isEmptyObject(missingFields)) {
			$missingFieldsNotice.hide();
			return;
		}

		// show notice
		$missingFieldsList.html('');
		for( var key in missingFields ) {
			var field = missingFields[key];
			var $listItem = $("<li></li>");
			$listItem.html( field.name + " (<code>" + field.tag + "</code>)");
			$listItem.appendTo( $missingFieldsList );
		}

		$missingFieldsNotice.show();
	}


	return {
		checkRequiredFields: checkRequiredFields
	}

};

module.exports = FormWatcher;