(function () {
	// Function to dynamically load a script
	function loadScript(url, callback) {
		var script = document.createElement("script");
		script.type = "text/javascript";
		script.src = url;
		script.onload = function () {
			console.log("Script loaded successfully.");
			if (callback) callback();
		};
		script.onerror = function () {
			console.error("Error loading script.");
		};
		document.head.appendChild(script);
	}

	// URL of the script you want to load
	var scriptUrl = "https://assets.quinn.live/woocommerce/quinn-story.bundle.js";

	// Load the script and print a message once it's loaded
	loadScript(scriptUrl, function () {
		document.addEventListener("DOMContentLoaded", function () {
			// Initialize and render the widget
			window.Quinn.functions.renderQuinn({
				widgetType: "story",
				target: "#quinn-story-1",
			});
		});
	});
})();
