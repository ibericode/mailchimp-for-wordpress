'use strict';

var lucy = function( site_url, algolia_app_id, algolia_api_key, algolia_index_name, defaultLinks, contactLink ) {

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
	element.setAttribute('class','lucy');
	document.body.appendChild(element);

	function maybeClose(e) {

		// close when pressing ESCAPE
		if(e.type === 'keyup' && e.keyCode == 27 ) {
			close();
		}

		// close when clicking ANY element outside of Lucy
		var element = event.target || event.srcElement;
		if(e.type === 'click' && typeof(element.matches) === "function" && ! element.matches('.lucy, .lucy--button, .lucy *') )  {
			close();
		}

	}

	function open() {
		isOpen = true;
		m.redraw();

		document.addEventListener('keyup', maybeClose);
		document.addEventListener('click', maybeClose);
	}

	function close() {
		isOpen = false;
		m.redraw();
		reset();

		document.removeEventListener('keyup', maybeClose);
		document.removeEventListener('click', maybeClose);
	}

	function reset() {
		searchQuery('');
		searchResults([]);
		m.redraw();
	}

	var module = {};
	module.view = function() {
		element.setAttribute('class', 'lucy ' + ( isOpen ? 'open' : 'closed' ) );

		if( isOpen ) {
			var header = m('div.header', [
				m('h4', 'Looking for help?'),
				m('form', {
					onsubmit: search
				}, [
					searchInput,m('input', {
						type: 'text',
						value: searchQuery(),
						oninput: function() {
							if( this.value === '' && searchQuery() !== '' ) {
								reset();
							}

							searchQuery(this.value);
						},
						placeholder: 'What are you looking for?'
					}),
					m('span', {
						class: 'loader',
						config: function(el) {
							loader = el;
						}
					}),
					m('input', { type: 'submit' })
				])]);

			if( searchQuery().length > 1 ) {
				var content = [
					(searchResults().length) ? searchResults().map(function(l) {
						return m('a', { href: l.href }, m.trust(l.text) );
					}) : m("em.search-pending","Hit [ENTER] to search for \""+ searchQuery() +"\"..")
				];
			} else {
				var content = [
					defaultLinks.map(function(l) {
						return m('a', { href: l.href }, m.trust(l.text) );
					})
					];
			}

			return m('div.lucy--content', [
				m('span.close-icon', { onclick: close }, ""),
				header,
				m('div.list', content),
				m('div.footer', [
					m("span", "Can't find the answer you're looking for?"),
					m("a", { class: 'button button-primary', href: contactLink }, "Contact Support")
				])
			]);
		}

		return m('span.lucy--button', { onclick: open }, "Need help?")
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

	m.mount(element,module);
};

module.exports = lucy;