<?php
/*
Plugin Name: Det Akademiske Kvarter (DAK) Events WordPress plugin
Description: DAK's plugin for showing evens from DAK's event repository
Version: 0.1
*/

define('DEW_PREFIX', dirname(realpath(__FILE__)));

// Include the client library for our remote events
require_once( DEW_PREFIX . '/eventsCalendarClient.php' );
require_once( DEW_PREFIX . '/dew_tools.php' );
require_once( DEW_PREFIX . '/dew_widget.php' );
require_once( DEW_PREFIX . '/dew_management.php' );
require_once( DEW_PREFIX . '/dew_calendar.php' );
require_once( DEW_PREFIX . '/dew_shortcode.php' );

function DakEventsWpInit () {
	wp_enqueue_script('dew_eventJsStyle', plugins_url('/dew_js.php?eventStylesheet=1', __FILE__), array('jquery'));
	wp_enqueue_script('dew_js_events', plugins_url('/js/events.js', __FILE__), array('jquery'));
	wp_enqueue_style('dew_mainStyle', plugins_url('/css/main.css', __FILE__));
	load_plugin_textdomain('dak_events_wp', false, dirname(plugin_basename(__FILE__)) . '/i18n');
}

function DakEventsWpAdminMenu () {
        $management = new DEW_Management();
	add_menu_page(__('DAK Events Calendar', 'dew'),__('DAK Events Calendar', 'dew'), 'activate_plugins', 'dak-events-calendar', array(&$management, 'options'));
}

function DakEventsWpAdminHeaderScript () {
}

if ( is_admin() ) {
	wp_enqueue_script("dew_js_widgetadmin", plugins_url('/js/widgetAdmin.js', __FILE__), array('jquery'));
}

// Remember to flush_rules() when adding rules
function dew_flushRules(){
	global $wp_rewrite;
	$wp_rewrite->flush_rules();
}

// Adding a new rule
function dew_insertMyRewriteRules($rules)
{
	$newrules = array();
	$options = get_option('optionsDakEventsWp');

	if (isset($options['rewriteEventUrlRegex']) && isset($rules[$options['rewriteEventUrlRegex']])) {
		unset($rules[$options['rewriteEventUrlRegex']]);
	}

	if (isset($options['rewriteFestivalUrlRegex']) && isset($rules[$options['rewriteFestivalUrlRegex']])) {
		unset($rules[$options['rewriteFestivalUrlRegex']]);
	}

	if (isset($options['eventPageId']) && ($options['eventPageId'] > 0)) {
		$page = get_page($options['eventPageId']);

		$options['rewriteEventUrlRegex'] = '(' . $page->post_name . ')/(\d+)(/(.*))?$';
		$newrules[$options['rewriteEventUrlRegex']] = 'index.php?pagename=$matches[1]&event_id=$matches[2]';

		$options['rewriteFestivalUrlRegex'] = '(' . $page->post_name . ')/festival/(\d+)/(/(.*))?$';
		$newrules[$options['rewriteFestivalUrlRegex']] = 'index.php?pagename=$matches[1]&festival_id=$matches[2]';
	}

	update_option('optionsDakEventsWp', $options);

	return $newrules + $rules;
}

// Adding the event_id var so that WP recognizes it
function dew_insertMyRewriteQueryVars($vars)
{
	if (!in_array('event_id', $vars)) {
		$vars[] = 'event_id';
	}

	if (!in_array('festival_id', $vars)) {
		$vars[] = 'festival_id';
	}
	return $vars;
}

function dewEventServerUrl () {
	/**
	 * Adds a <script> element to the head section.
	 * For use when you want to use the original event server
	 * ie. an api call
	 */
	$dewOptions = get_option('optionsDakEventsWp');
?>
<script type="text/javascript">/* <![CDATA[ */
var dewEventServerUrl = '<?php echo $dewOptions['eventServerUrl'] ?>';
/* ]]> */
</script>
<?php
}

function dewEditorButtons () {
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

function addDewEditorPlugin ($plugin_array) {
	$plugin_array['dewEditorExtra'] = plugins_url('/js/dewEditorExtraPlugin.js', __FILE__);
	return $plugin_array;
}

function registerDewEditorButtons ($buttons) {
	$buttons[] = 'separator';
	$buttons[] = 'dewShowPickerPopup';

	return $buttons;
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

add_action('init', 'DakEventsWpInit');
add_action('init', 'DewEditorButtons');
add_action('widgets_init', create_function('', 'return register_widget("DEW_Widget");') );
add_action('admin_head', 'DakEventsWpAdminHeaderScript');
add_action('admin_menu', 'DakEventsWpAdminMenu');

add_filter('rewrite_rules_array','dew_insertMyRewriteRules');
add_filter('query_vars','dew_insertMyRewriteQueryVars');

add_shortcode('dew_agenda', 'dew_agenda_shortcode_handler');
add_shortcode('dew_fullevent', 'dew_fullevent_shortcode_handler');
add_shortcode('dew_agenda_or_fullarrangement', 'dew_agenda_or_fullarrangement_shortcode_handler');
add_shortcode('dew_detailbox', 'dew_detailbox_shortcode_handler');
