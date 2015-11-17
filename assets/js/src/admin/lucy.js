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
	var isDragging = false;

	// create element
	var element = document.createElement('div');
	element.setAttribute('class','lucy');
	element.draggable = true;
	element.ondragstart = dragStart;
	document.body.appendChild(element);

	// get position from localStorage
	var position = localStorage.getItem('lucy_position');
	if( position ) {
		position = position.split(',');
		element.style.right = position[0] + "px";
		element.style.bottom = position[1] + "px";
	}

	document.addEventListener('dragover',dragOver);
	document.addEventListener('drop', drop );

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

	function dragOver(event) {
		if(!isDragging) return;
		event.preventDefault();
		return false;
	}

	function dragStart(event) {
		isDragging = true;
		event.dataTransfer.setData('text/plain','');
		event.dataTransfer.setData("text/plain", event.clientX + "," + event.clientY);
	}

	function drop(event) {
		if( ! isDragging ) return;
		event.preventDefault();
		event.returnValue = false;

		var offset = event.dataTransfer.getData("text/plain").split(',');
		var style = window.getComputedStyle(element, null);

		var x = event.clientX - parseInt(offset[0]);
		var y = event.clientY - parseInt(offset[1]);
		var bottom = ( parseInt( style.getPropertyValue("bottom") ) - y );
		var right = ( parseInt( style.getPropertyValue("right") ) - x );

		element.style.bottom = bottom + "px";
		element.style.right = right + "px";

		// update position in localStorage
		localStorage.setItem('lucy_position', right + "," + bottom );
		isDragging = false;
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
			m('span.lucy-button', {
				onclick: open, style: { display: isOpen ? 'none' : 'block' }
			}, [
				m('span.lucy-button-text',  "Need help?")
			])
		];
	};

	function showResults(results) {
		if( ! results.length ) {
			nothingFound = true;
		} else {
			searchResults(results.map(function(r) {
				return { href: r.path, text: r._highlightResult.title.value};
			}));
		}
	}


	function search(query) {
		loader.innerText = '.';
		loadingInterval = window.setInterval(function() {
			loader.innerText += '.';

			if( loader.innerText.length > 3 ) {
				loader.innerText = '.';
			}
		}, 333 );

		index.search( query, { hitsPerPage: 5 }, function( error, result ) {

			if( error ) {
				// TODO: show error
			 } else {
				showResults(result.hits);
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