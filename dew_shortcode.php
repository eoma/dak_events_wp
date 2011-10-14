<?php

/**
 * This file holds functions related to wordpress short codes.
 */

require_once( DEW_PREFIX . '/eventsCalendarClient.php' );
require_once( DEW_PREFIX . '/dew_tools.php' );
require_once( DEW_PREFIX . '/dew_template.php' );

add_shortcode('dew_agenda', 'dew_agenda_shortcode_handler');
add_shortcode('dew_fullevent', 'dew_fullevent_shortcode_handler');
add_shortcode('dew_fullfestival', 'dew_fullfestival_shortcode_handler');
add_shortcode('dew_agenda_menu', 'dew_agenda_menu_shortcode_handler');
add_shortcode('dew_agenda_or_arrangement', 'dew_agenda_or_arrangement_shortcode_handler');
add_shortcode('dew_detailbox', 'dew_detailbox_shortcode_handler');

function dew_calendar_shortcode_handler ($atts, $content = null, $code = "") {
	
}

function dew_detailbox_shortcode_handler ($atts, $content = null, $code = "") {
	/**
	 * Requires that the type and id attribute is set
	 * $atts can contain array(
	 *      'type' => 'event' or 'festival',
	 *      'id' => '1',
	 * )
	 */

	$validTypes = array('event', 'festival');

	if (!isset($atts['type']) || !in_array($atts['type'], $validTypes)) {
		return "<p>You must use [dew_detailbox type=&lt;type&gt; id=&lt;id&gt;], with type being either event or festival.</p>";
	}

	$type = $atts['type'];

	$options = DEW_Management::getOptions();
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);

	$queryArgs = array();

	if (!isset($atts['id'])) {
		return "<p>" . __('No id specified for detailbox', 'dak_events_wp') . "</p>";
	}

	$action = 'dew_render_';
	if ($type == 'event') {
		$action .= 'event_detailbox';
		$result = $client->event($atts['id']);
	} else if ($type == 'festival') {
		$action .= 'festival_detailbox';
		$result = $client->festival($atts['id']);
	}

	$arrangement = $result->data[0];
	$arrangement->startTimestamp = DEW_tools::dateStringToTime($arrangement->startDate, $arrangement->startTime);
	$arrangement->endTimestamp = DEW_tools::dateStringToTime($arrangement->endDate, $arrangement->endTime);

	ob_start();

	do_action($action, $arrangement);

	$bufferContent = ob_get_contents();
	ob_clean();

	return $bufferContent;
}


/**
 * Will output an agenda based on some filters
 */
function dew_agenda_shortcode_handler ($atts, $content = null, $code = "") {
	/**
	 * $atts can contain
	 * array(
	 *       'arranger_id' => '1,2,3,4',
	 *       'location_id' => '4,3,2,1',
	 *       'category_id' => '3,4,1,2',
	 *       'festival_id' => '1,2,3,2',
	 *       'no_current_events' => 1 or 0,
	 *       'compact_events' => 1 or 0,
	 *       'start_date' => '2011-05-01',
	 *       'end_date' => '2011-05-31',
	 *       'dayspan' => 7,
	 *       'title' => 'Events in may',
	 * )
	 */
	$options = DEW_Management::getOptions();
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);

	$queryArgs = array();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	//print_r($atts);
	
	$queryArgs['onlySummaries'] = 1;

	$atts = str_replace(array("\"", "'", "&quot;"), array('', '', ''), $atts);
	$config = array();

	if ( ! empty($atts['title']) )
		$config['title'] = trim($atts['title']);

	if ( ! empty($atts['arranger_id']) )
		$queryArgs['arranger_id'] = DEW_tools::stringToIntArray($atts['arranger_id']);
	
	if ( ! empty($atts['location_id']) )
		$queryArgs['location_id'] = DEW_tools::stringToIntArray($atts['location_id']);

	if ( ! empty($atts['category_id']) )
		$queryArgs['category_id'] = DEW_tools::stringToIntArray($atts['category_id']);

	if ( ! empty($atts['festival_id']) )
		$queryArgs['festival_id'] = DEW_tools::stringToIntArray($atts['festival_id']);

	if ( ! empty($atts['no_current_events']) )
		$queryArgs['noCurrentEvents'] = intval($atts['no_current_events']);

    if ( ! empty($atts['start_date']) )
		$queryArgs['startDate'] = strval($atts['start_date']);

    if ( ! empty($atts['end_date']) )
		$queryArgs['endDate'] = strval($atts['end_date']);

    if ( ! empty($atts['dayspan']) )
		$queryArgs['dayspan'] = intval($atts['dayspan']);

	if ( (isset($queryArgs['startDate']) && isset($queryArgs['endDate'])) || isset($queryArgs['dayspan']) ) {
		$results = $client->filteredEventsList($queryArgs, true);
	} else {
		$results = $client->filteredEventsList($queryArgs);
	}

	$dateSortedEvents = DEW_tools::groupEventsByDate($results->data, $options['dayStartHour']);	
	
	ob_start();

	do_action('dew_render_agenda', $dateSortedEvents, $config);

	$bufferContent = ob_get_clean();

	return $bufferContent;
}

function dew_fullevent_shortcode_handler ($atts, $template = null, $code = "") {
	/**
	 * $atts can contain
	 * array(
	 *       'event_id' => '1',
	 *       'exclude_metadata' => 1 or 0,
	 * )
	 */

	$options = DEW_Management::getOptions();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	if (empty($atts['event_id'])) {
		return __('No event_id attribute supplied to shortcode [dew_event]', 'dak_events_wp');
	}

	$eventResult = $client->event($atts['event_id']);
	$event = $eventResult->data[0];

	$formatConfig = array();

	if (isset($atts['no_title']) && ($atts['no_title'] == true)) {
		$formatConfig['no_title'] = true;
	}

	//var_dump($event);

	ob_start();

	do_action('dew_render_fullevent', $event);

	$bufferContent = ob_get_clean();

	return $bufferContent;
}

function dew_fullfestival_shortcode_handler ($atts, $template = null, $code = "") {
	/**
	 * $atts can contain
	 * array(
	 *       'festival_id' => '1',
	 *       'exclude_metadata' => '1',
	 * )
	 */

	$options = DEW_Management::getOptions();

	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);

	if (empty($atts['festival_id'])) {
		return __('No festival_id attribute supplied to shortcode [dew_festival]', 'dak_events_wp');
	}

	$festivalResult = $client->festival($atts['festival_id']);
	$festival = $festivalResult->data[0];

	$eventResult = $client->filteredEventsList(array(
		'festival_id' => $festival->id,
		'noCurrentEvents' => true
	));
	$dateSortedEvents = DEW_tools::groupEventsByDate($eventResult->data, $options['dayStartHour']);

	ob_start();

	do_action('dew_render_fullfestival', $festival, $dateSortedEvents);

	$bufferContent = ob_get_clean();

	return $bufferContent;
}

/**
 * $atts can be array(
 *   'dayspan' => any number bigger than or equal to 0
 * );
 */
function dew_agenda_menu_shortcode_handler ($atts = array(), $content = null, $code = "") {
	// $atts should contain dayspan, if not it will be set to 14
	global $wp_query;

	if (!isset($atts['dayspan'])) {
		$atts['dayspan'] = 14;
	} else {
		$atts['dayspan'] = intval($atts['dayspan']);
	}

	$menuElements = array(
		'upcoming' => array(
			'linkText' => sprintf(__('Next %d days', 'dak_events_wp'), $atts['dayspan']),
			'url' => DEW_tools::generateLinkToAgenda('upcoming'),
			'active' => false,
		),
		'archive' => array(
			'linkText' => __('Archive', 'dak_events_wp'),
			'url' => DEW_tools::generateLinkToAgenda('list'),
			'active' => false,
		),
		'nextMonths' => array(),
	);

	$locale = new WP_Locale();
		
	$dew_archive = null;

	if (!empty($_GET['dew_archive']) || $wp_query->get('dew_archive')) {
		$dew_archive = strval($wp_query->get('dew_archive'));
	}

	if (empty($dew_archive)) {
		$menuElements['upcoming']['active'] = true;
	} else if ($dew_archive == 'list') {
		$menuElements['archive']['active'] = true;
	}

	$currentMonth = intval(date('n'));
	$currentYear = intval(date('Y'));
	$queryYear = 0;
	$queryMonth = 0;

	$month = $currentMonth;
	$year = $currentYear;

	if (isset($dew_archive)) {
		$dateComponents = explode('-', $dew_archive);

		if (count($dateComponents) >= 2) {
			$queryYear = intval($dateComponents[0]);
			$queryMonth = intval($dateComponents[1]);
		}
	}

	for ($i = 0; $i < 4; $i++) {
		$monthName = ucfirst($locale->get_month($month));

		$menuElem = array(
			'linkText' => $monthName,
			'url' => DEW_tools::generateLinkToAgenda('month', array($year, $month)),
			'active' => false,
		);

		if (($year == $queryYear) && ($month == $queryMonth)) {
			$menuElem['active'] = true;
		}

		$menuElements['nextMonths'][] = $menuElem;

		if ($month == 12) {
			$month = 1;
			$year++;
		} else {
			$month++;
		}
	}

	ob_start();

	do_action('dew_render_agenda_menu', $menuElements);

	$bufferContent = ob_get_clean();

	return $bufferContent;
}

function dew_archive_list_shortcode_handler ($atts = array(), $content = null, $code = "") {
	$options = DEW_Management::getOptions();
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);

	$queryArgs = array();
	if ( ! empty($atts['arranger_id']) )
		$queryArgs['arranger_id'] = DEW_tools::stringToIntArray($atts['arranger_id']);

	if ( ! empty($atts['category_id']) )
		$queryArgs['category_id'] = DEW_tools::stringToIntArray($atts['category_id']);

	if ( ! empty($atts['location_id']) )
		$queryArgs['location_id'] = DEW_tools::stringToIntArray($atts['location_id']);

	$historyList = $client->filteredHistoryList($queryArgs);

	ob_start();

	do_action('dew_render_archive_list', $historyList->data);

	$bufferContent = ob_get_clean();

	return $bufferContent;
}

/**
 * $atts can be array(
 *   'arranger_id' => '1,2,3,4',
 *   'category_id' => '1,2,3,4',
 *   'location_id' => '1,2,3,4',
 *   'dayspan' => any number bigger than or equal to 0
 * );
 */
function dew_agenda_or_arrangement_shortcode_handler ($atts, $content = null, $code = "") {

	global $wp_query;

	if (!empty($_GET['event_id']) || $wp_query->get('event_id')) {
		$event_id = (empty($_GET['event_id'])) ? $wp_query->get('event_id') : $_GET['event_id'];

		$fullEventArgs = array(
			'event_id' => intval($event_id),
		);

		return dew_fullevent_shortcode_handler ($fullEventArgs, $content, $code);
	} elseif (!empty($_GET['festival_id']) || $wp_query->get('festival_id')) {
		$festival_id = (empty($_GET['festival_id'])) ? $wp_query->get('festival_id') : $_GET['festival_sid'];

		$fullFestivalArgs = array(
			'festival_id' => intval($festival_id),
		);

		return dew_fullfestival_shortcode_handler ($fullFestivalArgs, $content, $code);
	} else {
		$locale = new WP_Locale();

		if (!isset($atts['dayspan'])) {
			$atts['dayspan'] = 14;
		} else {
			$atts['dayspan'] = intval($atts['dayspan']);
		}

		$content = "";

		$config = array(
			'compact_view' => 1,
		);

		if (isset($atts['arranger_id']))
			$config['arranger_id'] = $atts['arranger_id'];

		if (isset($atts['category_id']))
			$config['category_id'] = $atts['category_id'];

		if (isset($atts['location_id']))
			$config['location_id'] = $atts['location_id'];

		$dew_archive = null;

		if (!empty($_GET['dew_archive']) || $wp_query->get('dew_archive')) {
			$dew_archive = strval($wp_query->get('dew_archive'));
		}

		if (!isset($atts['exclude_menu']) || ($atts['exclude_menu'] == 0)) {
			$content .= dew_agenda_menu_shortcode_handler(array('dayspan' => $atts['dayspan']));
		}

		if ($dew_archive == 'list') {
			$content .= dew_archive_list_shortcode_handler ($config);

		} else {
			
			if (!empty($dew_archive)) {
				$queryYear = null;
				$queryMonth = null;

				$dateComponents = explode('-', $dew_archive);

				if (count($dateComponents) >= 2) {
					$queryYear = intval($dateComponents[0]);
					$queryMonth = intval($dateComponents[1]);
				}

				if (($queryMonth >= 1) && ($queryMonth <= 12)) {
					$config['start_date'] = sprintf('%04d-%02d-01', $queryYear, $queryMonth);
					$config['end_date'] = sprintf('%04d-%02d-', $queryYear, $queryMonth);

					$config['end_date'] .= date('t' , strtotime($config['start_date']));
				}
				
				$config['title'] = 'Events in ' . $locale->get_month($queryMonth) . ' ' . $queryYear;
			} else {
				$config['title'] = 'Events for the next ' . $atts['dayspan'] . ' days.';
				$config['dayspan'] = $atts['dayspan'];
			}
			
			$content .= dew_agenda_shortcode_handler ($config);
		}

		return $content;
	}
}
