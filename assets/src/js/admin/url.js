function parse (url) {
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
}

function build (data) {
  const ret = []
  for (const d in data) { ret.push(d + '=' + encodeURIComponent(data[d])) }
  return ret.join('&')
}

function setParameter (url, key, value) {
  const data = parse(url)
  data[key] = value
  return build(data)
}

module.exports = { parse, build, setParameter }
