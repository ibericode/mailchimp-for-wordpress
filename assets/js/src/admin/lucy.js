'use strict';

var lucy = function( site_url, algolia_app_id, algolia_api_key, algolia_index_name ) {

	var links = [
		{
			text: "Knowledge Base",
			href: "https://mc4wp.com/kb/"
		}
	];
	var isOpen = false;
	var m = require('../../third-party/mithril.js');
	var algoliasearch = require( '../../third-party/algoliasearch.js');
	var client = algoliasearch( algolia_app_id, algolia_api_key );
	var index = client.initIndex( algolia_index_name );

	var loadingInterval = 0;
	var element = document.createElement('div');
	var loader, searchInput;
	var searchResults = m.prop([]);
	var searchQuery = m.prop('');
	element.setAttribute('class','lucy');// = 'lucy';
	document.body.appendChild(element);

	function open() {
		console.log("opening");
		isOpen = true;
		m.redraw();
	}

	function close() {
		isOpen = false;
		m.redraw();
	}

	var module = {};
	module.view = function() {
		element.setAttribute('class', 'lucy ' + ( isOpen ? 'open' : 'closed' ) );

		if( isOpen ) {
			return [
				m('span.close', { onclick: close }, "close"),
				m('form', {
					onsubmit: search
				}, [
					searchInput,m('input', {
						type: 'text',
						value: searchQuery(),
						onchange: m.withAttr('value', searchQuery),
						placeholder: 'What are you looking for?'
					}),
					m('span', {
						class: 'loader',
						config: function(el) {
							loader = el;
						}
					}),
					m('input', { type: 'submit' })
				]),
				searchResults().map(function(r) {
					return m('a', { href: r.href }, m.trust(r.text) );
				}),
				m('div', { class: 'lucy--links'}, links.map(function(link) {
					return m('a', { href: link.href }, link.text);
				}))
			];
		}

		return m('span.lucy--button', { onclick: open }, [
			m('span', "Looking for help?"),
			m('span', {class: 'dashicons dashicons-editor-help'})
		])
	};


	// create element and float it in bottom right corner

	function search(e) {
		e.preventDefault();
		loader.innerText = '.';
		loadingInterval = window.setInterval(function() {
			loader.innerText += '.';

			if( loader.innerText.length > 3 ) {
				loader.innerText = '.';
			}
		}, 333 );

		index.search( searchQuery(), { hitsPerPage: 5 }, function( error, result ) {

			searchResults(result.hits.map(function(r) {
				return { href: r.path, text: r._highlightResult.title.value};
			}));

			m.redraw();

			// clear loader
			loader.innerText = '';
			window.clearInterval(loadingInterval);
		} );


	}

	console.log(element);
	console.log(module);

	m.mount(element,module);
};

module.exports = lucy;