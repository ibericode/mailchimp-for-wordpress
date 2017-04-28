'use strict';

const URL = {
	parse: function(url) {
		let query = {};
        let a = url.split('&');
		for (let i in a) {
			if(!a.hasOwnProperty(i)) {
				continue;
			}
            let b = a[i].split('=');
			query[decodeURIComponent(b[0])] = decodeURIComponent(b[1]);
		}

		return query;
	},
	build: function(data) {
        let ret = [];
		for (let d in data)
			ret.push(d + "=" + encodeURIComponent(data[d]));
		return ret.join("&");
	},
	setParameter: function( url, key, value ) {
        let data = URL.parse( url );
		data[ key ] = value;
		return URL.build( data );
	}
};

module.exports = URL;