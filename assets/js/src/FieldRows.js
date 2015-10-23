var r = {};

r.label = function(config) {
	// label row
	return m("p", [
		m("label", "Field Label"),
		m("input.widefat", {
			type: "text",
			value: config.label(),
			onchange: m.withAttr('value', config.label)
		} )
	]);
};

r.defaultValue = function(config) {
	return m("p", [
		m("label", "Default Value"),
		m("input.widefat", {
			type: "text",
			value: config.defaultValue(),
			oninput: m.withAttr('value', config.defaultValue)
		} )
	]);
};


r.isRequired = function(config) {
	return m('p', [
		m('label.cb-wrap', [
			m('input', {
				type: 'checkbox',
				checked: config.isRequired(),
				onchange: m.withAttr( 'checked', config.isRequired )
			}),
			"Is this field required?"
		])
	]);
};

r.usePlaceholder = function(config) {
	return m("p", [
		m("label.cb-wrap", [
			m("input", {
				type: 'checkbox',
				checked: config.usePlaceholder(),
				onchange: m.withAttr( 'checked', config.usePlaceholder )
			}),
			"Use \""+ config.defaultValue() +"\" as placeholder for the field."
		])
	]);
};

r.useParagraphs = function(config) {
	return m('p', [
		m('label.cb-wrap', [
			m('input', {
				type: 'checkbox',
				checked: config.useParagraphs(),
				onchange: m.withAttr( 'checked', config.useParagraphs )
			}),
			"Wrap in paragraph tags?"
		])
	]);
};

module.exports = r;