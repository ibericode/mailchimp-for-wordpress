'use strict'

const URL = {
  parse: function (url) {
    const query = {}
    const a = url.split('&')
    for (const i in a) {
      if (!a.hasOwnProperty(i)) {
        continue
      }
      const b = a[i].split('=')
      query[decodeURIComponent(b[0])] = decodeURIComponent(b[1])
    }

    return query
  },
  build: function (data) {
    const ret = []
    for (const d in data) { ret.push(d + '=' + encodeURIComponent(data[d])) }
    return ret.join('&')
  },
  setParameter: function (url, key, value) {
    const data = URL.parse(url)
    data[key] = value
    return URL.build(data)
  }
}

module.exports = URL
