<?php
/*
Plugin Name: Det Akademiske Kvarter (DAK) Events WordPress plugin
Description: DAK's plugin for showing evens from DAK's event repository
Version: 0.1
*/

define('DEW_PREFIX', dirname(realpath(__FILE__)));
define('DEW_URL',  WP_PLUGIN_URL . '/dak_events_wp');

// Include the client library for our remote events
require_once( DEW_PREFIX . '/eventsCalendarClient.php' );
require_once( DEW_PREFIX . '/dew_tools.php' );
require_once( DEW_PREFIX . '/dew_widget.php' );
require_once( DEW_PREFIX . '/dew_management.php' );
require_once( DEW_PREFIX . '/dew_calendar.php' );
require_once( DEW_PREFIX . '/dew_shortcode.php' );

function DakEventsWpInit () {
	wp_enqueue_style('dew_mainStyle', DEW_URL . '/css/main.css');
	wp_enqueue_script('dew_js_events', DEW_URL . '/js/events.js', array('jquery'));
}

function DakEventsWpAdminMenu () {
        $management = new DEW_Management();
	add_menu_page('DAK Events Calendar','DAK Events Calendar', 'activate_plugins', 'dak-events-calendar', array(&$management, 'options'));
}

function DakEventsWpAdminHeaderScript () {
}

if ( is_admin() ) {
	wp_enqueue_script("dew_js_widgetadmin", DEW_URL . '/js/widgetAdmin.js', array('jquery'));
}

add_action('init', 'DakEventsWpInit');
add_action('widgets_init', create_function('', 'return register_widget("DEW_Widget");') );
add_action('admin_head', 'DakEventsWpAdminHeaderScript');
add_action('admin_menu', 'DakEventsWpAdminMenu');

add_shortcode('dew_agenda', 'dew_agenda_shortcode_handler');
add_shortcode('dew_event_detailbox', 'dew_event_detailbox_shortcode_handler');
