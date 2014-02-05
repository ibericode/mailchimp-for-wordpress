(function($) { 

	$("tr.pro-feature, tr.pro-feature td :radio").change(function() {
		this.checked = false;
		alert("This option is only available in the premium version of MailChimp for WordPress.");
		event.stopPropagation();
	});

	$("tr.pro-feature, tr.pro-feature label").click(function() {
		alert("This option is only available in the premium version of MailChimp for WordPress.");
		event.stopPropagation();
	});


	// Add buttons to QTags editor
	(function() {
		if(window.QTags == undefined) { return; }

		QTags.addButton( 'mc4wp_paragraph', '<p>', '<p>', '</p>', 'p', 'Paragraph tag', 1 );
		QTags.addButton( 'mc4wp_label', 'label', '<label>', '</label>', 'l', 'Label tag', 2 );
		QTags.addButton( 'mc4wp_subscriber_count', '# of subscribers', '{subscriber_count}', '', 's', 'Shows number of subscribers of selected list(s)' );
	})();


	/**
	* MailChimp for WordPress Field Wizard
	* Created by Danny van Kooten
	*/
	(function() {
		// setup variables
		var $lists = $("#mc4wp-lists :input");
		var $mailchimpFields = $("#mc4wp-fw-mailchimp-fields");
		var $mailchimpMergeFields = $("#mc4wp-fw-mailchimp-fields .merge-fields");
		var $mailchimpGroupings = $("#mc4wp-fw-mailchimp-fields .groupings");
		var $wizardFields = $("#mc4wp-fw-fields");
		var $value = $("#mc4wp-fw-value");
		var $valueLabel = $("#mc4wp-fw-value-label");
		var $multipleValues = $("#mc4wp-fw-values");
		var $label = $("#mc4wp-fw-label");
		var $placeholder = $("#mc4wp-fw-placeholder");
		var $required = $("#mc4wp-fw-required");
		var $wrapp = $("#mc4wp-fw-wrap-p");
		var field = {
			'type': 'text',
			'name': ''
		};
		var $codePreview = $("#mc4wp-fw-preview");
		// functions

		// set the fields the user can choose from
		function setFields()
		{
			// show notice if no lists selecteed
			var $selectedLists = $lists.filter(':checked');
			$(".no-lists-selected").toggle(($selectedLists.length == 0));
			

			// empty field select
			$mailchimpFields.find('option').not('.default').remove();
			
			// loop through checked lists
			$selectedLists.each(function() {
				var fields = $(this).data('fields');
				var groupings = $(this).data('groupings');

				// loop through merge fields from this list
				for(var i = 0; i < fields.length; i++) {
					var f = fields[i];

					// add field to select if no similar option exists yet
					if($mailchimpMergeFields.find("option[value='"+ f.tag +"']").length == 0) {

						var text = (f.name.length > 40) ? f.name.substring(0, 40) + '..' : f.name;
						if(f.req) { text += '*'; }

						// only show first 4 fields
						if((i <= 3)) {
							var $option = $("<option />").text(text).val(f.tag).data('field', f);
						} else {
							var $option = $("<option />").text("(PRO ONLY) " + text).val(f.tag).attr('disabled', 'disabled');
						}
						
						$mailchimpMergeFields.append($option);
					}
				}

				// loop through interest groupings
				for(var i = 0, groupingsCount = groupings.length; i < groupingsCount; i++) {
					var grouping = groupings[i];
					
					// add field to select if no similar option exists yet
					if($mailchimpGroupings.find("option[value='"+ grouping.id +"']").length == 0) {
						var text = (grouping.name.length > 40) ? grouping.name.substring(0, 40) + '..' : grouping.name;

						// only show 1 grouping
						if(i < 1) {
							var $option = $("<option />").text(text).val(grouping.id).data('grouping', grouping);
						} else {
							var $option = $("<option />").text("(PRO ONLY) " + text).val(grouping.id).attr('disabled', 'disabled');
						}

						$mailchimpGroupings.append($option);
					}
					

					
				}


			});
		}

		function setPresets()
		{
			resetFields();

			var selected = $(this).find(':selected');
			if(selected.val() == 'submit') {
				// setup values for submit field
				field['type'] = 'submit';
				$valueLabel.text("Button text");
				$wizardFields.find('p.row').filter('.value, .wrap-p').show();
				updateCodePreview();
			} else {
				var data = selected.data('field');
				if(data) { return setPresetsForField(data); }

				var data = selected.data('grouping');
				if(data) { return setPresetsForGrouping(data); }
			}
			return;
		}

		function resetFields() {
			$wizardFields.find('.row :input').each(function() {
				if($(this).is(":checkbox")) { this.checked = true; } else { this.value = ''; }
			});

			$wizardFields.find('p.row').hide();
			$multipleValues.find(':input').remove();
			$wizardFields.show();

			field['type'] = 'text';
			field['name'] = '';
			$valueLabel.html("Initial value <small>(optional)</small>");
		}

		function addGroupInputs(groups)
		{
			// add a text input to $multipleValues for each group
			for(var i = 0, groupsCount = groups.length; i < groupsCount; i++) {
				$("<input />").attr('type', 'text').addClass('widefat').data('value', groups[i].name).attr('placeholder', 'Label for "' + groups[i].name + '" (or leave empty)').attr('value', groups[i].name).appendTo($multipleValues);
			}
		}

		function setPresetsForGrouping(data)
		{
			$wizardFields.find('p.row').filter('.values, .label, .wrap-p').show();
			$label.val(data.name + ":");
			field['name'] = 'GROUPINGS['+ data.id + ']';
			addGroupInputs(data.groups);

			if(data.form_field == 'radio') {
				field['type'] = 'radio';
			} else if(data.form_field == 'dropdown') {
				field['type'] = 'select';
			} else if(data.form_field == 'hidden') {
				$wizardFields.find('p.row').filter('.values, .label, .wrap-p').hide();
				$wizardFields.find('p.row.value').show();
				field['type'] = 'hidden';

				for(var i = 0, groupsCount = data.groups.length; i < groupsCount; i++) {
					$value.val($value.val() + data.groups[i].name + ',');
				}

			} else {
				field['type'] = 'checkbox';
				field['name'] = 'GROUPINGS['+ data.id + '][]';
			}		

			// update code preview
			updateCodePreview();
		}


		// show available fields and fill it with some values
		function setPresetsForField(data) 
		{

			// show fields for this field type
			var visibleRowsMap = {
				'default': [ 'label', 'value', 'placeholder', 'required', 'wrap-p' ],
				'select': [ 'label', 'required', 'wrap-p', 'values'],
				'radio': [ 'label', 'required', 'wrap-p', 'values'],
				'date':  [ 'label', 'required', 'wrap-p', 'value']
			}

			var fieldTypesMap = {
				'text': 'text', 'email': 'email', 'phone': 'tel', 'address': 'text', 'number': 'number',
				'dropdown': 'select', 'date': 'date', 'birthday': 'date', 'radio': 'radio',  'checkbox': 'checkbox'
			}

			if(fieldTypesMap[data.field_type] != undefined) {
				var fieldType = fieldTypesMap[data.field_type];
			} else {
				var fieldType = 'text';
			}

			if(visibleRowsMap[fieldType] != undefined) {
				var visibleRows = visibleRowsMap[fieldType];
			} else {
				var visibleRows = visibleRowsMap["default"];
			}

			for(var i = 0, count = visibleRows.length; i < count; i++) {
				$wizardFields.find('p.row.' + visibleRows[i]).show();
			}

			// populate fields with preset values
			field['type'] = fieldType;
			field['name'] = data.tag;
			$placeholder.val("Your " + data.name.toLowerCase());
			$label.val(data.name + ":");
			$required.attr('checked', data.req);
			if($multipleValues.is(":visible") && data.choices) {
				for(var i = 0, count = data.choices.length; i < count; i++) {
					$("<input />").attr('type', 'text').addClass('widefat').data('value', data.choices[i]).attr('placeholder', 'Label for "' + data.choices[i] + '" (or leave empty)').attr('value', data.choices[i]).appendTo($multipleValues);
				}
			}
			
			// update code preview
			updateCodePreview();
		}

		function updateCodePreview()
		{
			var $code = $("<div></div>");
			var inputs = [];
			var $input;

			// build input / select / textarea element
			if(field['type'] == 'select') {
				$input = $("<select />");

				// add options to select
				$multipleValues.find(":input").each(function() {
					if($(this).val().length > 0) {
						$el = $("<option />").val($(this).data("value")).text($(this).val());
						$el.appendTo($input);
					}					
				});

			} else if(field['type'] == 'radio' || field['type'] == 'checkbox') {

				// build multiple input values
				$multipleValues.find(":input").each(function() {
					if($(this).val().length > 0) {
						$input = $("<input />").attr('type', field['type']).attr('name', field.name).val($(this).data('value'));

						if($required.is(':visible:checked')) {
							$input.attr('required', 'required');
						}

						$code.append($input);

						$input.wrap("<label />");
						$("<span />").text($(this).val() + ' ').insertAfter($input);
					}					
				});

			} else if(field['type'] == 'textarea') {
				$input = $("<textarea />");
			} else {
				$input = $("<input />").attr('type', field['type']);
			}

			// only do this piece when we're not adding radio inputs
			if(field['type'] != 'radio' && field['type'] != 'checkbox') {

				// set name attribute
				if(field.name.length > 0) {
					$input.attr('name', field.name);
				}

				// set value
				if($value.is(":visible") && $value.val().length > 0) {
					if(field['type'] == 'textarea') {
						$input.text($value.val());
					} else {
						$input.attr('value', $value.val());
					}
				}

				// add placeholder to element
				if($placeholder.is(":visible") && $placeholder.val().length > 0) {
					$input.attr('placeholder', $placeholder.val());
				}

				// add required attribute
				if($required.is(':visible:checked')) {
					$input.attr('required', 'required');
				}

				$code.append($input);

			
			}

			// build label
			if($label.is(":visible") && $label.val().length > 0) {
				$("<label />").text($label.val()).prependTo($code);
			}
			
			// start indenting and tabbing of code
			var codePreview = $code.html();

			if($wrapp.is(':visible:checked')) {
				$code.wrapInner($("<p />"));

				// indent code inside paragraphs (double tab)
				codePreview = $code.html()
					.replace(/<p>/gi, "<p>\n\t")
					.replace(/<label><input /gi, "\n\t<label><input ")
					.replace(/<\/label><input/gi, "</label> \n\t<input")
					.replace(/<select /gi, "\n\t<select ")
					.replace(/<\/select>/gi, "\n\t</select>")
					.replace(/<\/span><\/label>/gi, "</span>\n\t</label> \n")
					.replace(/<option /gi, "\n\t\t<option ")
					.replace(/<label><input type="radio"/g, "<label>\n\t\t<input type=\"radio\"")
					.replace(/<label><input type="checkbox"/g, "<label>\n\t\t<input type=\"checkbox\"")
					.replace(/<span>/gi, "\n\t\t<span>")
			} else {
				// indent code, single tab
				codePreview = codePreview
					.replace(/<option /gi, "\n\t<option ")
					.replace(/<label><input type="radio"/g, "<label>\n\t<input type=\"radio\"")
					.replace(/<label><input type="checkbox"/g, "<label>\n\t<input type=\"checkbox\"")
					.replace(/<span>/gi, "\n\t<span>");
			}

			// newline after every closed element
			codePreview = codePreview.replace(/></g, "> \n<");			

			// add code to codePreview textarea
			$codePreview.val(codePreview);
		}

		function addCodeToFormMarkup() {
			
			var result = false;

			// try to insert in QuickTags editor at cursor position
			if(typeof QTags !='undefined' && QTags.insertContent) {
				result = QTags.insertContent($codePreview.val());
			}
			
			// fallback
			if(!result) {
				$("#mc4wpformmarkup").val($("#mc4wpformmarkup").val() + "\n" + $codePreview.val());
			}
		}

		// setup events
		$lists.change(setFields);
		$mailchimpFields.change(setPresets);
		$wizardFields.change(updateCodePreview);
		$("#mc4wp-fw-add-to-form").click(addCodeToFormMarkup);

		// init
		setFields();

	})();

})(jQuery);

