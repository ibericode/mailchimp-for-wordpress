// Tabs
var Tabs = function(context) {
	'use strict';

	// @todo last piece of jQuery... can we get rid of it?
	var $ = window.jQuery;

	var $context = $(context);
	var $tabs = $context.find('.tab');
	var $tabNavs = $context.find('.nav-tab');
	var refererField = context.querySelector('input[name="_wp_http_referer"]');
	var tabs = [];

	$.each($tabs, function(i,t) {
		var id = t.id.substring(4);
		var title = $(t).find('h2').first().text();

		tabs.push({
			id: id,
			title: title
		});
	});

	var URL = {
		parse: function(url) {
			var query = {};
			var a = url.split('&');
			for (var i in a) {
				if(!a.hasOwnProperty(i)) {
					continue;
				}
				var b = a[i].split('=');
				query[decodeURIComponent(b[0])] = decodeURIComponent(b[1]);
			}

			return query;
		},
		build: function(data) {
			var ret = [];
			for (var d in data)
				ret.push(d + "=" + encodeURIComponent(data[d]));
			return ret.join("&");
		},
		setParameter: function( url, key, value ) {
			var data = URL.parse( url );
			data[ key ] = value;
			return URL.build( data );
		}
	};

	function open( tab, updateState ) {

		// make sure we have a tab object
		if(typeof(tab) === "string"){
			tab = tabs.filter(function(t) {
				return t.id === tab;
			}).pop();
		}

		if(!tab || !tab.id) { return false; }

		// should we update state?
		updateState = updateState !== false;

		// hide all tabs & remove active class
		$tabs.removeClass('tab-active').css('display', 'none');
		$tabNavs.removeClass('nav-tab-active');

		// add `nav-tab-active` to this tab
		var nav = document.getElementById('nav-tab-'+tab.id);
		nav.className += " nav-tab-active";
		nav.blur();

		// show target tab
		var targetTab = document.getElementById("tab-" + tab.id);
		targetTab.style.display = 'block';
		targetTab.className += " tab-active";

		// create new URL
		var url = URL.setParameter(window.location.href, "tab", tab.id );

		// update hash
		if( history.pushState && updateState ) {
			history.pushState( tab, '', url );
		}

		// update document title
		title(tab);

		// update referer field
		refererField.value = url;

		// if thickbox is open, close it.
		if( typeof(tb_remove) === "function" ) {
			tb_remove();
		}

		return true;
	}

	function title(tab) {
		var title = document.title.split('-');
		document.title = document.title.replace(title[0], tab.title + " ");
	}

	function switchTab(e) {
		e = e || window.event;

		var tabId = this.getAttribute('data-tab');
		if( ! tabId ) {
			var urlParams = URL.parse( this.href );
			if( typeof(urlParams.tab) === "undefined" ) {
				return;
			}

			tabId = urlParams.tab;
		}

		var opened = open( tabId );

		if( opened ) {
			e.preventDefault();
			e.returnValue = false;
			return false;
		}

		return true;
	}

	function init() {

		// check for current tab
		var activeTab = $tabs.filter(':visible').get(0);

		var tab = tabs.filter(function(t) {
			return t.id === activeTab.id.substring(4);
		}).pop();

		if(!tab) return;

		// check if tab is in html5 history
		if(history.replaceState && history.state === null) {
			history.replaceState( tab, '' );
		}

		// update document title
		title(tab);
	}

	$tabNavs.click(switchTab);
	$context.on('click', '.tab-link', switchTab);

	if(window.addEventListener) {
	 	init();

		window.addEventListener('popstate', function(e) {
			if(!e.state) return true;
			var tab = e.state;
			return open(tab,false);
		});
	}

	return {
		open: open
	}

};

module.exports = Tabs;