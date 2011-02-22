<?php

/**
 * This file holds functions related to wordpress short codes.
 */

require_once( DEW_PREFIX . '/eventsCalendarClient.php' );
require_once( DEW_PREFIX . '/dew_tools.php' );
require_once( DEW_PREFIX . '/dew_format.php' );

function dew_calendar_shortcode_handler ($atts, $content = null, $code = "") {
	
}

function dew_event_detailbox_shortcode_handler ($atts, $content = null, $code = "") {
	$options = get_option('optionsDakEventsWp');
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	$queryArgs = array();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	if (!isset($atts['id'])) {
		return "<p>" . __('No event id specified', 'dak_events_wp') . "</p>";
	}
	
	$result = $client->event($atts['id']);

	if ($result->totalCount == 0) {
		return "<p>" . sprintf(__('No event found with specified id %d.', 'dak_events_wp'), $atts['id']) . "</p>";
	}

	$event = $result->data[0];

	$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);
	$endTimestamp = DEW_tools::dateStringToTime($event->endDate, $event->endTime);

	if ($event->startDate == $event->endDate) {
		$day = ucfirst($locale->get_weekday(date('w', $startTimestamp )));
		$date = sprintf(__('%s %s from %s to %s', 'dak_events_wp'),
			$day,
			date($dateFormat, $startTimestamp),
			date($timeFormat, $startTimestamp),
			date($timeFormat, $endTimestamp)
		);
	} else {
		$startDay = ucfirst($locale->get_weekday(date('w', $startTimestamp )));
		$endDay = ucfirst($locale->get_weekday(date('w', $endTimestamp )));
		$date = sprintf(__('%s %s %s from %s to %s %s %s', 'dak_events_wp'),
			$startDay,
			date($dateFormat, $startTimestamp),
		        date($timeFormat, $startTimestamp),
		        $endDay,
			date($dateFormat, $endTimestamp),
			date($timeFormat, $endTimestamp)
		);
	}

	$location = DEW_tools::getLocationFromEvent($event);

	$categories = '';
	foreach ($event->categories as $c) {
		$categories .= $c->name . ', ';
	}
	$categories = substr($categories, 0, -2);

	$output =  DEW_tools::sprintfn(DEW_format::eventDetailBox(), array(
		'title' => $event->title,
		'date' => $date,
		'location' => $location,
		'arranger' => $event->arranger->name,
		'category' => $categories,
	));

	return $output;
}

/**
 * Will output the agenda of the next 20 events
 */
function dew_agenda_shortcode_handler ($atts, $content = null, $code = "") {
	/**
	 * $atts can contain
	 * array(
	 *       'arranger_id' => '1,2,3,4',
	 *       'location_id' => '4,3,2,1',
	 *       'category_id' => '3,4,1,2',
	 * )
	 */
	$options = get_option('optionsDakEventsWp');
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	$queryArgs = array();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	//print_r($atts);

	$atts = str_replace(array("\"", "'", "&quot;"), array('', '', ''), $atts);

	if ( ! empty($atts['arranger_id']) ) 
		$queryArgs['arranger_id'] = DEW_tools::stringToIntArray($atts['arranger_id']);
	
	if ( ! empty($atts['location_id']) ) 
		$queryArgs['location_id'] = DEW_tools::stringToIntArray($atts['location_id']);

	if ( ! empty($atts['category_id']) ) 
		$queryArgs['category_id'] = DEW_tools::stringToIntArray($atts['category_id']);

	$results = $client->filteredEventsList($queryArgs);

	$dateSortedEvents = DEW_tools::groupEventsByDate($results->data);

	$eventFormat = DEW_format::agendaFullEvent();
	
	$output = "<div class='dew_agenda'>\n";

	foreach ($dateSortedEvents as $timestamp => $events) {
		$startDayName = ucfirst($locale->get_weekday(date('w', $timestamp )));
		
		$output .= "<h2><span class='agenda_day_name'>" . $startDayName . "</span> <span class='agenda_day_number'>" . date('j', $timestamp) . "</span><span class='agenda_month_name'>" . date('F', $timestamp) . "</span></h2>\n";

		$output .= "<div class='event_date_list'>\n";

		foreach($events as $event) {
			$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);
			$endTimestamp = DEW_tools::dateStringToTime($event->endDate, $event->endTime);

			if ($event->startDate == $event->endDate) {
				$renderedDate = sprintf(__('%s from %s to %s', 'dak_events_wp'),
					date($dateFormat, $startTimestamp),
					date($timeFormat, $startTimestamp),
					date($timeFormat, $endTimestamp)
				);
			} else {
				$renderedDate = sprintf(__('%s from %s to %s %s', 'dak_events_wp'),
					date($dateFormat, $startTimestamp),
					date($timeFormat, $startTimestamp),
					date($dateFormat, $endTimestamp),
					date($timeFormat, $endTimestamp)
				);
			}

			$location = DEW_tools::getLocationFromEvent($event);

			$categories = '';
			foreach ($event->categories as $c) {
				$categories .= $c->name . ', ';
			}
			$categories = substr($categories, 0, -2);

			$output .= DEW_tools::sprintfn($eventFormat, array(
				'title' => $event->title,
				'leadParagraph' => $event->leadParagraph,
				'description' => $event->description,
				'renderedDate' => $renderedDate,
				'location' => $location,
				'arranger' => $event->arranger->name,
				'category' => $categories,
				'startTime' => date($timeFormat, $startTimestamp)
			));

		}

		$output .= "</div>\n";
	}

	//$output .= '<button type="button" class="dew_agenda_loadExtra">Load more</button>';

	$output .= "</div>\n";

	return $output;
}

function dew_fullevent_shortcode_handler ($atts, $content = null, $code = "") {
	/**
	 * $atts can contain
	 * array(
	 *       'event_id' => '1',
	 * )
	 */

	$options = get_option('optionsDakEventsWp');

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	$eventResult = $client->event($atts['event_id']);
	$event = $eventResult->data[0];

	$eventFormat = DEW_format::fullEvent();

	//var_dump($event);

	$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);
	$endTimestamp = DEW_tools::dateStringToTime($event->endDate, $event->endTime);

	if ($event->startDate == $event->endDate) {
		$renderedDate = sprintf(__('%s from %s to %s', 'dak_events_wp'),
			date($dateFormat, $startTimestamp),
			date($timeFormat, $startTimestamp),
			date($timeFormat, $endTimestamp)
		);
	} else {
		$renderedDate = sprintf(__('%s from %s to %s %s', 'dak_events_wp'),
			date($dateFormat, $startTimestamp),
			date($timeFormat, $startTimestamp),
			date($dateFormat, $endTimestamp),
			date($timeFormat, $endTimestamp)
		);
	}

	$location = DEW_tools::getLocationFromEvent($event);

	$categories = '';
	foreach ($event->categories as $c) {
		$categories .= $c->name . ', ';
	}
	$categories = substr($categories, 0, -2);

	$allowedHtml = array(
		'a' => array('href'),
		'p' => array(),
		'span' => array(),
		'b' => array(),
		'strong' => array(),
		'em' => array(),
		'i' => array(),
		'blockquote' => array(),
		'ul' => array(),
		'ol' => array(),
		'li' => array(),
		'br' => array(),
	);

	$output = DEW_tools::sprintfn($eventFormat, array(
		'title' => $event->title,
		'leadParagraph' => $event->leadParagraph,
		'description' => wp_kses($event->description, $allowedHtml),
		'renderedDate' => $renderedDate,
		'location' => $location,
		'arranger' => $event->arranger->name,
		'category' => $categories,
		'startTime' => date($timeFormat, $startTimestamp),
		'urlOriginal' => $event->url,
	));

	return $output;
}

function dew_agenda_or_fullevent_shortcode_handler ($atts, $content = null, $code = "") {
	if (!empty($_GET['event'])) {
		return dew_fullevent_shortcode_handler (array('event_id' => intval($_GET['event'])), $content, $code);
	} else {
		return dew_agenda_shortcode_handler ($atts, $content, $code);
	}
}
