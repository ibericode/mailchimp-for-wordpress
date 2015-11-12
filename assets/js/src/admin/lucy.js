'use strict';

var lucy = function( site_url, algolia_app_id, algolia_api_key, algolia_index_name, defaultLinks, contactLink ) {

	var m = require('../../third-party/mithril.js');
	var algoliasearch = require( '../../third-party/algoliasearch.js');
	var client = algoliasearch( algolia_app_id, algolia_api_key );
	var index = client.initIndex( algolia_index_name );

	var isOpen = false;
	var loader, loadingInterval = 0;
	var searchResults = m.prop([]);
	var searchQuery = m.prop('');

	var element = document.createElement('div');
	element.setAttribute('class','lucy');
	document.body.appendChild(element);

	function addEvent(element,event,handler) {
		if(element.addEventListener){
			element.addEventListener(event,handler,false);
		} else {
			element.attachEvent('on' + event, handler);
		}
	}

	function removeEvent(element,event,handler){
		if(element.removeEventListener()){
			element.removeEventListener(event,handler);
		} else {
			element.detachEvent('on' + event, handler);
		}
	}

	function maybeClose(e) {

		// close when pressing ESCAPE
		if(e.type === 'keyup' && e.keyCode == 27 ) {
			close();
			return;
		}

		// close when clicking ANY element outside of Lucy
		var clickedElement = event.target || event.srcElement;
		if(e.type === 'click' && element.contains && ! element.contains(clickedElement) )  {
			close();
		}

	}

	function open() {
		isOpen = true;
		m.redraw();

		addEvent(document,'keyup',maybeClose);
		addEvent(document,'click',maybeClose);
	}

	function close() {
		isOpen = false;
		reset();

		removeEvent(document,'keyup',maybeClose);
		removeEvent(document,'click',maybeClose);
	}

	function reset() {
		searchQuery('');
		searchResults([]);
		m.redraw();
	}

	var module = {};
	module.view = function() {
		var content;

		element.setAttribute('class', 'lucy ' + ( isOpen ? 'open' : 'closed' ) );

		if( searchResults().length > 1 ) {
			content = m('div.search-results', [
				(searchResults().length) ? searchResults().map(function(l) {
					return m('a', { href: l.href }, m.trust(l.text) );
				}) : m("em.search-pending","Hit [ENTER] to search for \""+ searchQuery() +"\"..")
			]);
		} else {
			content = m("div.links", defaultLinks.map(function(l) {
				return m('a', { href: l.href }, m.trust(l.text) );
			}));
		}

		return [
			m('div.lucy--content', { style: { display: isOpen ? 'block' : 'none' } }, [
				m('span.close-icon', { onclick: close }, ""),
				m('div.header', [
					m('h4', 'Looking for help?'),
					m('form', {
						onsubmit: search
					}, [
						m('input', {
							type: 'text',
							value: searchQuery(),
							oninput: function() {
								if( this.value === '' && searchQuery() !== '' ) {
									reset();
								}

								searchQuery(this.value);
							},
							config: function(el) { isOpen && el.focus(); },
							placeholder: 'What are you looking for?'
						}),
						m('span', {
							"class": 'loader',
							config: function(el) {
								loader = el;
							}
						}),
						m('input', { type: 'submit' })
					])
				]),
				m('div.list', content),
				m('div.footer', [
					m("span", "Can't find the answer you're looking for?"),
					m("a", { "class": 'button button-primary', href: contactLink }, "Contact Support")
				])
			]),
			m('span.lucy-button', { onclick: open, style: { display: isOpen ? 'none' : 'block' } }, [
				m('span.lucy-button-text',  "Need help?")
			])
		];
	};


	// create element and float it in bottom right corner
	function search(e) {
		e.preventDefault();
		e.returnValue = false;

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

		return false;
	}

	m.mount(element,module);
};

module.exports = lucy;