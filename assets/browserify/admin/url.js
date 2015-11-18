'use strict';

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

module.exports = URL;