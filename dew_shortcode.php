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
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache']);
	$locale = new WP_Locale();

	$queryArgs = array();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	if (!isset($atts['id'])) {
		return "<p>No event id specified</p>";
	}
	
	$result = $client->event($atts['id']);

	if ($result->totalCount == 0) {
		return "<p>No event found with specified id " . $atts['id'] . "</p>";
	}

	$event = $result->data[0];

	$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);
	$endTimestamp = DEW_tools::dateStringToTime($event->endDate, $event->endTime);

	if ($event->startDate == $event->endDate) {
		$day = ucfirst($locale->get_weekday(date('w', $startTimestamp )));
		$date = date($dateFormat, $startTimestamp) 
		        . ' from ' . date($timeFormat, $startTimestamp) . ' to ' 
		        . date($timeFormat, $endTimestamp);
	} else {
		$startDay = ucfirst($locale->get_weekday(date('w', $startTimestamp )));
		$endDay = ucfirst($locale->get_weekday(date('w', $endTimestamp )));
		$date = $startDay . ' ' . date($dateFormat, $startTimestamp) 
		        . ' from ' . date($timeFormat, $startTimestamp) . ' to ' 
		        . $endDay . ' ' . date($dateFormat, $endTimestamp) . ' ' .  date($timeFormat, $endTimestamp);
	}

	$location = DEW_tools::getLocationFromEvent($event);

	$categories = '';
	foreach ($event->categories as $c) {
		$catgegories .= $c->name . ', ';
	}

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
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache']);
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

	$eventFormat = DEW_format::fullEvent();
	
	$output = "<div class='dew_agenda'>\n";

	foreach ($dateSortedEvents as $timestamp => $events) {
		$startDayName = ucfirst($locale->get_weekday(date('w', $timestamp )));
		
		$output .= "<h2>" . $startDayName . ' ' . date($dateFormat, $timestamp) . "</h2>\n";

		foreach($events as $event) {
			$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);
			$endTimestamp = DEW_tools::dateStringToTime($event->endDate, $event->endTime);

			if ($event->startDate == $event->endDate) {
				$renderedDate = date($dateFormat, $startTimestamp)
				          . ' from ' . date($timeFormat, $startTimestamp) . ' to '
				          . date($timeFormat, $endTimestamp);
			} else {
				$renderedDate = date($dateFormat, $startTimestamp) 
				        . ' from ' . date($timeFormat, $startTimestamp) . ' to '
				        . date($dateFormat, $endTimestamp) . ' ' .  date($timeFormat, $endTimestamp);
			}

			$location = DEW_tools::getLocationFromEvent($event);

			$categories = '';
			foreach ($event->categories as $c) {
				$catgegories .= $c->name . ', ';
			}

			$output .= DEW_tools::sprintfn($eventFormat, array(
				'title' => $event->title,
				'leadParagraph' => $event->leadParagraph,
				'description' => $event->description,
				'renderedDate' => $renderedDate,
				'location' => $location,
				'arranger' => $event->arranger->name,
				'category' => $categories,
			));

		}

	}

	$output .= '<button type="button" class="dew_agenda_loadExtra">Load more</button>';

	$output .= "</div>\n";

	return $output;
}
