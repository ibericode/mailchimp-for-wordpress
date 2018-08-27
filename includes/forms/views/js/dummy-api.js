(function() {
	if (!window.pl4wp) {
		window.pl4wp = {
			listeners: [],
			forms    : {
				on: function (event, callback) {
					window.pl4wp.listeners.push({
						event   : event,
						callback: callback
					});
				}
			}
		}
	}
})();
