'use strict'

// dependencies
import tlite from 'tlite'
const tabs = require('./admin/tabs.js')
const settings = require('./admin/settings.js')
const helpers = require('./admin/helpers.js')

tlite(el => el.className.indexOf('mc4wp-tooltip') > -1)

require('./admin/list-fetcher.js')
require('./admin/fields/mailchimp-api-key.js')
require('./admin/list-overview.js')
require('./admin/show-if.js')

// expose some things
window.mc4wp = window.mc4wp || {}
window.mc4wp.helpers = helpers
window.mc4wp.settings = settings
window.mc4wp.tabs = tabs
