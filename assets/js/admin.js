(function($) {
	'use strict';

	/**
	 * Variables
	 */
	var $context = $(document.getElementById('mc4wp-admin'));
	var $listInputs = $(document.getElementById('mc4wp-lists')).find(':input');
	var $formMarkup = $(document.getElementById('mc4wpformmarkup'));
	var $missingFieldsNotice = $(document.getElementById('missing-fields-notice'));
	var $missingFieldsList = $(document.getElementById('missing-fields-list'));

	/**
	 * Functions
	 */
	function showProNotice() {

		// prevent checking of radio buttons
		if( typeof this.checked === 'boolean' ) {
			this.checked = false;
		}

		alert( mc4wp.strings.proOnlyNotice );
		event.stopPropagation();
	}

	function toggleSendWelcomeFields() {

		var $el = $(document.getElementById('mc4wp-send-welcome'));

		if($(this).val() == 0) {
			$el.removeClass('hidden').find(':input').removeAttr('disabled');
		} else {
			$el.addClass('hidden').find(':input').attr('disabled', 'disabled').prop('checked', false);
		}
	}

	function toggleWooCommerceSettings() {
		console.log( $(this).prop('checked'));
		var $el = $(document.getElementById('woocommerce-settings'));
		$el.toggle(this.checked);
	}

	function toggleFieldWizard() {
		var hasListSelected = $listInputs.filter(':checked').length > 0;
		$(".mc4wp-notice.no-lists-selected").toggle( ! hasListSelected );
		$( document.getElementById( 'mc4wp-fw-fields')).toggle( hasListSelected );
		$( document.getElementById( 'mc4wp-fw-mailchimp-fields' )).toggle( hasListSelected );
	}

	function allowTabKey(e) {
		var keyCode = e.keyCode || e.which;

		if (keyCode === 9) {
			e.preventDefault();
			var start = this.selectionStart;
			var end = this.selectionEnd;

			// set textarea value to: text before caret + tab + text after caret
			$(this).val($(this).val().substring(0, start) + "\t" + $(this).val().substring(end));

			// put caret at right position again
			this.selectionStart = this.selectionEnd = start + 1;
		}
	}

	function addQTagsButtons() {
		if ( typeof(QTags) === 'undefined' ) {
			return;
		}

		QTags.addButton( 'mc4wp_paragraph', '<p>', '<p>', '</p>', 'paragraph', 'Paragraph tag', 1 );
		QTags.addButton( 'mc4wp_label', 'label', '<label>', '</label>', 'label', 'Label tag', 2 );
		QTags.addButton( 'mc4wp_response', 'form response', '{response}', '', 'response', 'Shows the form response' );
		QTags.addButton( 'mc4wp_subscriber_count', '# of subscribers', '{subscriber_count}', '', 'subscribers', 'Shows number of subscribers of selected list(s)' );

		if( window.mc4wp.hasCaptchaPlugin === true ) {
			QTags.addButton( 'mc4wp_captcha', 'CAPTCHA', '{captcha}', '', 'captcha', 'Display a CAPTCHA field' );
		}
	}

	/**
	 * Bind Event Handlers
	 */

	// show a notice when clicking a pro feature
	$context.find(".pro-feature, .pro-feature label, .pro-feature :radio").click(showProNotice);

	// Show send-welcome field only when double opt-in is disabled
	$context.find('input[name$="[double_optin]"]').change(toggleSendWelcomeFields);

	// show woocommerce settings only when `show at woocommerce checkout` is checked.
	$context.find('input[name$="[show_at_woocommerce_checkout]"]').change(toggleWooCommerceSettings);

	// only show fieldwizard when a list is selected
	$listInputs.change(toggleFieldWizard);

	// Allow tabs inside the form mark-up
	$(document).delegate('#mc4wpformmarkup', 'keydown', allowTabKey);

	addQTagsButtons();


	/**
	* MailChimp for WordPress Field Wizard
	* Created by Danny van Kooten
	*/
	(function() {
		'use strict';

		// setup variables
		var $lists = $("#mc4wp-lists :input");
		var $mailchimpFields = $("#mc4wp-fw-mailchimp-fields");
		var $mailchimpMergeFields = $mailchimpFields.find('.merge-fields');
		var $mailchimpGroupings = $mailchimpFields.find(".groupings");
		var $wizardFields = $("#mc4wp-fw-fields");
		var $value = $("#mc4wp-fw-value");
		var $valueLabel = $("#mc4wp-fw-value-label");
		var $multipleValues = $("#mc4wp-fw-values");
		var $label = $("#mc4wp-fw-label");
		var $placeholder = $("#mc4wp-fw-placeholder");
		var $required = $("#mc4wp-fw-required");
		var $wrapp = $("#mc4wp-fw-wrap-p");
		var fieldType, fieldName;
		var $codePreview = $("#mc4wp-fw-preview");
		var strings = mc4wp.strings.fieldWizard;
		var requiredFields = [];

		// functions
		function checkRequiredFields() {

			// check presence of reach required field
			var missingFields = {};
			for(var i=0; i<requiredFields.length; i++) {
				var htmlString = 'name="' + requiredFields[i].tag.toLowerCase();
				if( $formMarkup.val().toLowerCase().indexOf( htmlString ) == -1 ) {
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
			return;
		}

		// set the fields the user can choose from
		function setMailChimpFields()
		{
			// empty field select
			$mailchimpFields.find('option').not('.default').remove();

			// empty required fields array
			requiredFields = [];
			
			// loop through checked lists
			$lists.filter(':checked').each(function() {
				var listId = $(this).val();
				var list = mc4wp.mailchimpLists[listId];

				// loop through merge fields from this list
				for(var i = 0, fieldCount = list.merge_vars.length; i < fieldCount; i++) {

					var listField = list.merge_vars[i];

					if( listField.req ) {
						requiredFields.push( listField );
					}

					// add field to select if no similar option exists yet
					if($mailchimpMergeFields.find("option[value='"+ listField.tag +"']").length === 0) {

						var text = (listField.name.length > 25) ? listField.name.substring(0, 25) + '..' : listField.name;
						if(listField.req) { text += '*'; }

						var $option = $("<option />")
							.text(text)
							.val(listField.tag)
							.data('list-field', listField);

						// only enable 3 fields
						if( i > 3 ) {
							$option.text( strings.proOnly + " " + text)
								.attr('disabled', 'disabled')
								.data('field', null);
						}

						$mailchimpMergeFields.append($option);
					}
				}

				// loop through interest groupings
				for(var i = 0, groupingsCount = list.interest_groupings.length; i < groupingsCount; i++) {
					var listGrouping = list.interest_groupings[i];

					// add field to select if no similar option exists yet
					if($mailchimpGroupings.find("option[value='"+ listGrouping.id +"']").length === 0) {
						var text = (listGrouping.name.length > 25) ? listGrouping.name.substring(0, 25) + '..' : listGrouping.name;
						
						// build option HTML
						var $option = $("<option />")
							.text(text)
							.val(listGrouping.id)
							.data('list-grouping', listGrouping);

						// only show 1 grouping
						if(i >= 1) {
							$option.text( strings.proOnly + " " + text)
								.attr('disabled', 'disabled')
								.data('list-grouping', null);
						}

						$mailchimpGroupings.append($option);
					}
				}


			});

			// check required fields with new selected lists
			checkRequiredFields();
		}

		/**
		* Set Presets
		*/ 
		function setPresets()
		{
			resetFields();

			var selected = $(this).find(':selected');
			switch( selected.val() ) {

				case '_action':
					fieldType = 'action';
					$wizardFields.find('.wrap-p').show();
					break;

				case 'submit':
					fieldType = 'submit';
					$valueLabel.text( strings.buttonText );
					$value.val( strings.subscribe );
					$wizardFields.find('p.row').filter('.value, .wrap-p').show();
					break;

				case 'lists':
					fieldType = 'lists';
					$wizardFields.find('.wrap-p').show();
					break;

				default:
					// try data for MailChimp field
					var data = selected.data('list-field');
					if(data) { 
						return setPresetsForField(data); 
					}

					// try data for interest grouping
					var data = selected.data('list-grouping');
					if(data) { 
						return setPresetsForGrouping(data); 
					}

					break;
			}

			updateCodePreview();
		}

		/**
		* Resets all wizard fields back to their default state
		*/
		function resetFields() {
			$wizardFields.find('.row :input').each(function() {
				if($(this).is(":checkbox")) { this.checked = true; } else { this.value = ''; }
			});

			$wizardFields.find('p.row').hide();
			$multipleValues.find(':input').remove();
			$wizardFields.show();

			fieldType = 'text';
			fieldName = '';
			$valueLabel.html( strings.initialValue + " <small>" + strings.optional + "</small>" );
		}

		/**
		* Add inputs for each group
		*/
		function addGroupInputs(groups)
		{
			// add a text input to $multipleValues for each group
			for(var i = 0, groupsCount = groups.length; i < groupsCount; i++) {
				$("<input />").attr('type', 'text')
					.addClass('widefat').data('value', groups[i].name)
					.attr('placeholder', strings.labelFor + ' "' + groups[i].name + '" ' + strings.orLeaveEmpty )
					.attr('value', groups[i].name)
					.appendTo($multipleValues);
			}
		}

		/**
		* Set presets for interest groupings
		*/
		function setPresetsForGrouping(data)
		{
			$wizardFields.find('p.row').filter('.values, .label, .wrap-p').show();
			$label.val(data.name + ":");
			fieldName = 'GROUPINGS[' + data.id + ']';
			addGroupInputs(data.groups);

			switch(data.form_field) {
				case 'radio':
					fieldType = 'radio';
					break;

				case 'hidden':
					// hide all rows except value row
					$wizardFields.find('p.row').filter('.values, .label, .wrap-p').hide();
					$wizardFields.find('p.row.value').show();

					// add group name to hidden input value
					for(var i = 0, groupsCount = data.groups.length; i < groupsCount; i++) {
						$value.val($value.val() + data.groups[i].name + ',');
					}

					fieldType = 'hidden';
					break;

				case 'dropdown':
					fieldType = 'select';
					break;

				default:
					fieldType = 'checkbox';

					// turn field name into an array
					fieldName += '[]';
					break;
			}	

			// update code preview
			updateCodePreview();
		}

		/**
		* Build list choice HTML
		*/
		function getListChoiceHTML()
		{
			var html = '';
			$lists.each(function() {
				var list_id = $(this).val();
				var list_name = $(this).parent('label').text();
				var attrs = '';

				if($(this).is(':checked')) {
					attrs += 'checked ';
				}

				html += '<label>' + "\n"
				html += "\t" + '<input type="checkbox" name="_mc4wp_lists[]" value="' + list_id +'" '+ attrs + ' /> '+ list_name + "\n";
				html += '</label>' + "\n";
			});

			return html;
		}

		function getActionChoiceHTML() {
			var actions = [
				{ name: "subscribe", label: strings.subscribe, checked: true },
				{ name: "unsubscribe", label: strings.unsubscribe, checked: false }
			];

			var html = '';
			for( var i=0; i<actions.length; i++ ) {
				var action = actions[i];

				html += '<label>' + "\n";
				html += "\t" + '<input type="radio" name="_mc4wp_action" value="' + action.name + '" '+ ( ( action.checked ) ? 'checked' : '' ) +' > ' + action.label + "\n";
				html += '</label>' + "\n";
			}

			return html;
		}



		/**
		* Set presets for a fields
		*/
		function setPresetsForField(data) 
		{

			// show fields for this field type
			var visibleRowsMap = {
				'default': [ 'label', 'value', 'placeholder', 'required', 'wrap-p' ],
				'select': [ 'label', 'required', 'wrap-p', 'values'],
				'radio': [ 'label', 'required', 'wrap-p', 'values'],
				'date':  [ 'label', 'required', 'wrap-p', 'value']
			};

			// map MailChimp field types to HTML5 field type
			var fieldTypesMap = {
				'text': 'text', 'email': 'email', 'phone': 'tel', 'address': 'text', 'number': 'number',
				'dropdown': 'select', 'date': 'date', 'birthday': 'date', 'radio': 'radio',  'checkbox': 'checkbox'
			};

			if( typeof(fieldTypesMap[data.field_type]) !== "undefined") {
				fieldType = fieldTypesMap[data.field_type];
			} else {
				fieldType = 'text';
			}
			
			if( typeof(visibleRowsMap[fieldType]) !== "undefined") {
				var visibleRows = visibleRowsMap[fieldType];
			} else {
				var visibleRows = visibleRowsMap["default"];
			}

			for(var i = 0; i < visibleRows.length; i++) {
				$wizardFields.find('p.row.' + visibleRows[i]).show();
			}

			fieldType = fieldType;
			fieldName = data.tag;

			// set placeholder text
			$placeholder.val( "Your " + data.name.toLowerCase());

			// set label text
			$label.val( data.name + ":" );

			// set required attribute
			$required.attr('checked', data.req);

			if($multipleValues.is(":visible") && data.choices) {
				for(var i = 0; i < data.choices.length; i++) {
					$("<input />")
						.attr('type', 'text')
						.addClass('widefat')
						.data('value', data.choices[i])
						.attr('placeholder', strings.labelFor + ' "' + data.choices[i] + '" ' + strings.orLeaveEmpty )
						.attr('value', data.choices[i])
						.appendTo($multipleValues);
				}
			}
			
			// update code preview
			updateCodePreview();
		}

		/**
		* Format and indent the generated HTML
		* Then add it to the code preview textarea
		*/
		function setCodePreview(html) {
			html = html_beautify(html);
			$codePreview.val(html);
		}


		/**
		* Generate HTML based on the various field values
		*/
		function updateCodePreview()
		{
			var $code = $("<div></div>");
			var $input;

			switch(fieldType) {

				case 'action':
					var html = getActionChoiceHTML();

					if( wrapInParagraph() ) {
						html = "<p>" + html + "</p>";
					}

					return setCodePreview(html);
					break;

				// MailChimp lists
				case 'lists':
					var html = getListChoiceHTML();

					if( wrapInParagraph() ) {
						html = "<p>" + html + "</p>";
					}

					return setCodePreview(html);
					break;

				// MailChimp dropdown
				case 'select':
					$input = $("<select />");

					// add options to select
					$multipleValues.find(":input").each(function() {
						if($(this).val().length > 0) {
							$("<option />")
								.val($(this).data("value"))
								.text($(this).val())
								.appendTo($input);						}
					});
					break;

				// MailChimo choices
				case 'radio':
				case 'checkbox':
					// build multiple input values
					$multipleValues.find(":input").each(function() {
						if($(this).val().length > 0) {
							$input = $("<input />").attr('type', fieldType).attr('name', fieldName).val($(this).data('value'));

							if($required.is(':visible:checked')) {
								$input.attr('required', true);
							}

							$code.append($input);

							$input.wrap("<label />");
							$("<span />").text($(this).val() + ' ').insertAfter($input);
						}					
					});
					break;

				// MailChimp long text
				case 'textarea':
					$input = $("<textarea />");
					break;

				default:
					$input = $("<input />").attr('type', fieldType);
					break;
			}

			// only do this piece when we're not adding radio inputs
			if(fieldType !== 'radio' && fieldType !== 'checkbox') {

				// set name attribute
				if(fieldName.length > 0) {
					$input.attr('name', fieldName);
				}

				// set value
				if($value.is(":visible") && $value.val().length > 0) {
					if(fieldType === 'textarea') {
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
					$input.attr('required', true);
				}

				$code.append($input);			
			}

			// build label
			if($label.is(":visible") && $label.val().length > 0) {
				$("<label />").text($label.val()).prependTo($code);
			}

			// wrap in paragraphs?
			if( wrapInParagraph() ) {
				$code.wrapInner($("<p />"));
			}
			
			setCodePreview($code.html());
		}

		/**
		* Transfer code preview field to form mark-up
		*/
		function addCodeToFormMarkup() {
			
			var result = false;

			// try to insert in QuickTags editor at cursor position
			if(typeof wpActiveEditor !== 'undefined' && typeof QTags !== 'undefined' && QTags.insertContent) {
				result = QTags.insertContent($codePreview.val());
			}
			
			// fallback, just append
			if(!result) {
				$formMarkup.val($formMarkup.val() + "\n" + $codePreview.val());
			}

			// trigger change event
			$formMarkup.change();
		}

		/**
		 * Should we wrap the HTML in paragraph tags?
		 *
		 * @returns {boolean}
		 */
		function wrapInParagraph() {
			return ( $wrapp.is(':visible:checked') ) ? true: false;
		}

		// setup events
		$lists.change(setMailChimpFields);
		$mailchimpFields.change(setPresets);
		$wizardFields.change(updateCodePreview);
		$("#mc4wp-fw-add-to-form").click(addCodeToFormMarkup);

		// Validate the form fields after every change
		$formMarkup.bind({
			'input': function() {
				$formMarkup.unbind('keydown');
				checkRequiredFields.call(this);
			},
			'keydown': checkRequiredFields
		});

		// init
		setMailChimpFields();

	})();

})(jQuery);

