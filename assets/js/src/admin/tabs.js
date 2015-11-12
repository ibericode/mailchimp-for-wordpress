// Tabs
var Tabs = function(context) {

	// @todo last piece of jQuery... can we get rid of it?
	var $ = window.jQuery;

	var $context = $(context);
	var $tabs = $context.find('.tab');
	var $tabNavs = $context.find('.nav-tab');
	var refererField = context.querySelector('input[name="_wp_http_referer"]');

	var URL = {
		parse: function(url) {
			var query = {};
			var a = url.split('&');
			for (var i in a) {
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

	function open( tab ) {

		// hide all tabs & remove active class
		$tabs.css('display', 'none');
		$tabNavs.removeClass('nav-tab-active');

		// add `nav-tab-active` to this tab
		$tabNavs.filter('#nav-tab-'+tab).addClass('nav-tab-active').blur();

		// show target tab
		var targetId = "tab-" + tab;
		var targetTab = document.getElementById(targetId);

		if( ! targetTab ) {
			return false;
		}

		targetTab.style.display = 'block';

		// create new URL
		var url = URL.setParameter(window.location.href, "tab", tab );

		// update hash
		if( history.pushState ) {
			history.pushState( '', '', url );
		}

		// update referer field
		refererField.value = url;

		// if thickbox is open, close it.
		if( typeof(tb_remove) === "function" ) {
			tb_remove();
		}

		return true;
	}


	function switchTab(e) {

		var tab = this.getAttribute('data-tab');
		if( ! tab ) {
			var urlParams = URL.parse( this.href );
			if( typeof(urlParams.tab) === "undefined" ) {
				return;
			}

			tab = urlParams.tab;
		}

		var opened = open( tab );

		if( opened ) {
			e.preventDefault();
			e.returnValue = false;
			return false;
		}

		return true;
	}

	$tabNavs.click(switchTab);
	$context.on('click', '.tab-link', switchTab);

	return {
		open: open
	}

};

module.exports = Tabs;