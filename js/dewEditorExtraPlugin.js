/**
 * Plugin for TinyMCE allowing you to embed events or festivals in posts
 */

(function () {

	var DOM = tinymce.DOM;

	tinymce.create('tinymce.plugins.dewEditorExtra', {
		/**
		 * Initializes the plugin, this will be executed after the plugin has been created.
		 * This call is done before the editor instance has finished it's initialization so use the onInit event
		 * of the editor instance to intercept that event.
		 *
		 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
		 * @param {string} url Absolute URL to where the plugin is located.
		 */
		init : function (ed, url) {
			var t = this;
			//var disabled = true;

			// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');

			ed.addCommand('dewShowPickerPopup', function() {
				//var shortcode = t.formatShortcode('eventDetailbox', {event_id: 32});
				
				ed.windowManager.open({
					id : 'dewPickerPopupBox',
					width : 480,
					height : "auto",
					wpDialog : true,
					title : ed.getLang('advlink.link_desc')
				}, {
					plugin_url : url // Plugin absolute URL
				});
			});

			// Register example button
			ed.addButton('dewShowPickerPopup', {
				title : ed.getLang('advanced.link_desc'),
				cmd : 'dewShowPickerPopup'
			});
		},

		/**
		 * Returns information about the plugin as a name/value array.
		 * The current keys are longname, author, authorurl, infourl and version.
		 *
		 * @return {Object} Name/value array containing information about the plugin.
		 */
		getInfo : function() {
			return {
				longname : 'DAK Events Calendar',
				author : 'Det Akademiske Kvarter',
				authorurl : 'https://github.com/eoma/dak_events_wp',
				infourl : '',
				version : "1.0"
			};
		}
	});

	tinymce.PluginManager.add('dewEditorExtra', tinymce.plugins.dewEditorExtra);
})();
