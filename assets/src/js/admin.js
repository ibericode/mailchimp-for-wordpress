// dependencies
const tabs = require('./admin/tabs.js')
const settings = require('./admin/settings.js')

require('./admin/list-fetcher.js')
require('./admin/fields/mailchimp-api-key.js')
require('./admin/list-overview.js')
require('./admin/show-if.js')

// expose some things
window.mc4wp = window.mc4wp || {}
window.mc4wp.settings = settings
window.mc4wp.tabs = tabs
