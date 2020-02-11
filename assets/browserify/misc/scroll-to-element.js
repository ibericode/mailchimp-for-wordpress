function scrollTo (element) {
  const x = window.pageXOffset || document.documentElement.scrollLeft
  const y = calculateScrollOffset(element)
  window.scrollTo(x, y)
}

function calculateScrollOffset (elem) {
  const body = document.body
  const html = document.documentElement
  const elemRect = elem.getBoundingClientRect()
  const clientHeight = html.clientHeight
  const documentHeight = Math.max(body.scrollHeight, body.offsetHeight,
    html.clientHeight, html.scrollHeight, html.offsetHeight)

  const scrollPosition = elemRect.bottom - clientHeight / 2 - elemRect.height / 2
  const maxScrollPosition = documentHeight - clientHeight
  return Math.min(scrollPosition + window.pageYOffset, maxScrollPosition)
}

module.exports = scrollTo
