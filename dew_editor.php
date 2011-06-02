<?php

function dewEditorPlugin () {
	if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) {
		return False;
	}

	if (is_admin() && (get_user_option('rich_editing') == true)) {
		add_filter('mce_external_plugins', 'addDewEditorPlugin');
		add_filter('mce_buttons', 'registerDewEditorButtons');
		add_action('tiny_mce_preload_dialogs', 'dewEditorPluginPopup');

		wp_enqueue_script("dew_js_pickerpopup", plugins_url('/js/dewPickerPopup.js', __FILE__), array('jquery'));
		add_action('admin_head', 'dewEventServerUrl');
	}
}

function dewEditorPluginPopup () {
?>
<div id="dewPickerPopupBox">
 <form id="dewPickerPopup" tabindex="-1">
  <div id="pickerPopupLeftPane">
   <select id="pickerPopupElementType">
    <option value="events" selected="selected">Events</option>
    <option value="festivals">Festivals</option>
   </select>
   <button id="pickerPopupAddElement" type="button" disabled="disabled">Add event</button><br />
   <span id="pickerPopupDescription">Please select an element to the right to use in the post</span>
  </div>
  <div id="pickerPopupRightPane">
   <ul id="pickerPopupList">
   </ul>
   <button type="button" id="pickerPopupLoadMoreElements" disabled="disabled" type="button">Load more events...</button>
  </div>
 </form>
</div>
<?php
}

function addDewEditorPlugin ($plugin_array) {
	$plugin_array['dewEditorExtra'] = plugins_url('/js/dewEditorExtraPlugin.js', __FILE__);
	return $plugin_array;
}

function registerDewEditorButtons ($buttons) {
	$buttons[] = 'separator';
	$buttons[] = 'dewShowPickerPopup';

	return $buttons;
}
