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
	var nothingFound = false;

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
		if(element.removeEventListener){
			element.removeEventListener(event,handler);
		} else {
			element.detachEvent('on' + event, handler);
		}
	}

	function maybeClose(event) {
		event = event || window.event;

		// close when pressing ESCAPE
		if(event.type === 'keyup' && event.keyCode == 27 ) {
			close();
			return;
		}

		// close when clicking ANY element outside of Lucy
		var clickedElement = event.target || event.srcElement;
		if(event.type === 'click' && element.contains && ! element.contains(clickedElement) )  {
			close();
		}

	}

	function open() {
		if( isOpen ) return;
		isOpen = true;
		m.redraw();

		addEvent(document,'keyup',maybeClose);
		addEvent(document,'click',maybeClose);
	}

	function close() {
		if( ! isOpen ) return;
		isOpen = false;
		reset();

		removeEvent(document,'keyup',maybeClose);
		removeEvent(document,'click',maybeClose);
	}

	function reset() {
		searchQuery('');
		searchResults([]);
		nothingFound = false;
		m.redraw();
	}

	var module = {};
	module.view = function() {
		var content;

		element.setAttribute('class', 'lucy ' + ( isOpen ? 'open' : 'closed' ) );

		if( searchQuery().length > 0 ) {
			if( searchResults().length > 0 ) {
				content = m('div.search-results', searchResults().map(function(l) {
					return m('a', { href: l.href }, m.trust(l.text) );
				}));
			} else {
				content = m('div.search-results', [
					m("em.search-pending", (nothingFound ? "Nothing found for " : "Hit [ENTER] to search for" ) + "\""+ searchQuery() +"\"..")
				]);
			}
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
					m('div.search-form', {
						onsubmit: search
					}, [
						m('input', {
							type: 'text',
							value: searchQuery(),
							onkeyup: function(event) {
								event = event || window.event;

								if( this.value === '' && searchQuery() !== '' ) {
									return reset();
								}

								searchQuery(this.value);

								if(event.keyCode == 13 ) {
									return search(this.value);
								}
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


	function search(query) {
		loader.innerText = '.';
		loadingInterval = window.setInterval(function() {
			loader.innerText += '.';

			if( loader.innerText.length > 3 ) {
				loader.innerText = '.';
			}
		}, 333 );

		index.search( query, { hitsPerPage: 5 }, function( error, result ) {

			if( ! result.hits.length ) {
				nothingFound = true;
			} else {
				searchResults(result.hits.map(function(r) {
					return { href: r.path, text: r._highlightResult.title.value};
				}));
			}

			m.redraw();

			/* clear loader */
			loader.innerText = '';
			window.clearInterval(loadingInterval);
		} );

	}

	m.mount(element,module);
};

module.exports = lucy;