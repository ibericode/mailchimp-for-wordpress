function EventEmitter () {
  this.listeners = {}
}

EventEmitter.prototype.emit = function (event, args) {
  this.listeners[event] = this.listeners[event] ?? []
  this.listeners[event].forEach(f => f.apply(null, args))
}

EventEmitter.prototype.on = function (event, func) {
  this.listeners[event] = this.listeners[event] ?? []
  this.listeners[event].push(func)
}

module.exports = EventEmitter
