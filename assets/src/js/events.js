/**
 * Create a new EventEmitter that stores its own set of listeners
 * @constructor
 */
function EventEmitter () {
  this.listeners = {}
}

/**
 * Emit the event with the given name and arguments
 * @param {string} event
 * @param {array} args
 */
EventEmitter.prototype.emit = function (event, args) {
  this.listeners[event] = this.listeners[event] ?? []
  this.listeners[event].forEach(f => f.apply(null, args))
}

/**
 * Attach a new listener to the given event name.
 * @param {string} event
 * @param {function} func
 */
EventEmitter.prototype.on = function (event, func) {
  this.listeners[event] = this.listeners[event] ?? []
  this.listeners[event].push(func)
}

module.exports = EventEmitter
