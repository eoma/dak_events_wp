<?php

/**
 * This file holds functions related to wordpress short codes.
 */

require_once( DEW_PREFIX . '/eventsCalendarClient.php' );
require_once( DEW_PREFIX . '/dew_tools.php' );

function dew_calendar_shortcode_handler ($atts, $content = null, $code = "") {
	
}

function dew_event_detailbox_shortcode_handler ($atts, $content = null, $code = "") {
	$options = get_option('optionsDakEventsWp');
	$client = new eventsCalendarClient ($options['eventServerUrl']);
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

	$output = <<<EOT
<p>
<strong>When:</strong> {$date} <br />
<strong>Location:</strong> {$location} <br />
<strong>Arranger:</strong> {$event->arranger->name}<br />
<strong>Category:</strong> {$event->category->name}
</p>
EOT;

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
	$client = new eventsCalendarClient ($options['eventServerUrl']);
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
	
	$output = "<div class='dew_agenda'>\n";

	foreach ($dateSortedEvents as $timestamp => $events) {
		$startDayName = ucfirst($locale->get_weekday(date('w', $timestamp )));
		
		$output .= "<h2>" . $startDayName . ' ' . date($dateFormat, $timestamp) . "</h2>\n";

		foreach($events as $event) {
			$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);
			$endTimestamp = DEW_tools::dateStringToTime($event->endDate, $event->endTime);

			$output .= "<h3>" . $event->title . "</h3>\n";
			$output .= $event->leadParagraph . "\n";
			$output .= $event->description . "\n";

			$output .= "<p>\n";

			if ($event->startDate == $event->endDate) {
				$output .= '<strong>When?</strong> ' . date($dateFormat, $startTimestamp) 
				        . ' from ' . date($timeFormat, $startTimestamp) . ' to ' 
				        . date($timeFormat, $endTimestamp) . '<br />';
			} else {
				$output .= '<strong>When?</strong> ' . date($dateFormat, $startTimestamp) 
				        . ' from ' . date($timeFormat, $startTimestamp) . ' to ' 
				        . date($dateFormat, $endTimestamp) . ' ' .  date($timeFormat, $endTimestamp) . '<br />';
			}
			$output .= '<strong>Where?</strong> ' . DEW_tools::getLocationFromEvent($event) . '<br />' . "\n";
			$output .= '<strong>Who?</strong> ' . $event->arranger->name . '<br />' . "\n";
			$output .= '<strong>What?</strong> ' . $event->category->name . "\n";

			$output .= "</p>\n";
		}

	}

	$output .= '<button type="button" class="dew_agenda_loadExtra">Load more</button>';

	$output .= "</div>\n";

	return $output;
}
