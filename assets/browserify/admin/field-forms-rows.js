var rows = function(m, i18n) {
	'use strict';

	var r = {};

	r.label = function (config) {
		// label row
		return m("div", [
			m("label", i18n.fieldLabel),
			m("input.widefat", {
				type       : "text",
				value      : config.label(),
				onchange   : m.withAttr('value', config.label),
				placeholder: config.title()
			})
		]);
	};

	r.defaultValue = function (config) {
		return m("div", [
			m("label", i18n.defaultValue),
			m("input.widefat", {
				type   : "text",
				value  : config.value(),
				onkeyup: m.withAttr('value', config.value)
			})
		]);
	};

	r.numberMinMax = function (config) {
		return m('div', [
			m('div.row', [
				m('div.col.col-3', [
					m('label', i18n.min),
					m('input', {type: 'number', onchange: m.withAttr('value', config.min)})
				]),
				m('div.col.col-3', [
					m('label', i18n.max),
					m('input', {type: 'number', onchange: m.withAttr('value', config.max)})
				])
			])
		])
	};


	r.isRequired = function (config) {
		return m('div', [
			m('label.cb-wrap', [
				m('input', {
					type    : 'checkbox',
					checked : config.required(),
					onchange: m.withAttr('checked', config.required)
				}),
				i18n.isFieldRequired
			])
		]);
	};

	r.usePlaceholder = function (config) {

		if (config.value().length > 0) {
			return m("div", [
				m("label.cb-wrap", [
					m("input", {
						type    : 'checkbox',
						checked : config.placeholder(),
						onchange: m.withAttr('checked', config.placeholder)
					}),
					i18n.placeholderDescription.replace('%s', config.value())
				])
			]);
		}
	};

	r.useParagraphs = function (config) {
		return m('div', [
			m('label.cb-wrap', [
				m('input', {
					type    : 'checkbox',
					checked : config.wrap(),
					onchange: m.withAttr('checked', config.wrap)
				}),
				i18n.wrapInParagraphTags
			])
		]);
	};

	r.choiceType = function (config) {
		return m('div', [
			m('label', i18n.choiceType ),
			m('select', {
				value   : config.type(),
				onchange: m.withAttr('value', config.type)
			}, [
				m('option', {
					value   : 'select',
					selected: config.type() === 'select' ? 'selected' : false
				}, i18n.dropdown ),
				m('option', {
					value   : 'radio',
					selected: config.type() === 'radio' ? 'selected' : false
				}, i18n.radioButtons ),
				m('option', {
					value   : 'checkbox',
					selected: config.type() === 'checkbox' ? 'selected' : false
				}, i18n.checkboxes )
			])
		]);
	};

	r.choices = function (config) {


		return m('div', [
			m('label', i18n.choices ),
			m('div.limit-height', [
				m("table", [

					// table body
					config.choices().map(function (choice, index) {
						return m('tr', {
							'data-id': index
						}, [
							m('td.cb', m('input', {
									name    : 'selected',
									type    : (config.type() === 'checkbox' ) ? 'checkbox' : 'radio',
									onchange: m.withAttr('value', config.selectChoice.bind(config)),
									checked: choice.selected(),
									value: choice.value()
								})
							),
							m('td.stretch', m('input.widefat', {
								type       : 'text',
								value      : choice.label(),
								placeholder: choice.title(),
								onchange   : m.withAttr('value', choice.label)
							})),
							m('td', m('span', {
								"class": 'dashicons dashicons-no-alt hover-activated',
								onclick: function (key) {
									this.choices().splice(key, 1);
								}.bind(config, index)
							}, ''))
						])
					})
				]) // end of table
			]) // end of limit-height div
		]);
	};

	return r;
};

module.exports = rows;