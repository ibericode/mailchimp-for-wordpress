// Tabs
var Tabs = function( $context ) {

	var $ = window.jQuery;
	var $tabs = $context.find('.tab');
	var $tabNav = $context.find('.nav-tab');
	var $tabLinks = $context.find('.tab-link');
	var $refererField = $context.find('input[name="_wp_http_referer"]');

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
		$tabs.hide();
		$tabNav.removeClass('nav-tab-active');

		// add `nav-tab-active` to this tab
		$(document.getElementById('nav-tab-' + tab )).addClass('nav-tab-active').blur();

		// show target tab
		var targetId = "tab-" + tab;
		document.getElementById(targetId).style.display = 'block';

		// create new URL
		var url = URL.setParameter(window.location.href, "tab", tab );

		// update hash
		if( history.pushState ) {
			history.pushState( '', '', url );
		}

		// update referer field
		$refererField.val(url);

		// if thickbox is open, close it.
		if( typeof(tb_remove) === "function" ) {
			tb_remove();
		}

		// focus on codemirror textarea, this fixes bug with blank textarea
		window.form_editor.refresh();
	}


	function switchTab() {

		var urlParams = URL.parse( this.href );
		if( typeof(urlParams.tab) === "undefined" ) {
			return;
		}

		open( urlParams.tab );

		// prevent page jump
		return false;
	}

	// add tab listener
	$tabNav.click(switchTab);
	$tabLinks.click(switchTab);

	return {
		open: open
	}

};

module.exports = Tabs;