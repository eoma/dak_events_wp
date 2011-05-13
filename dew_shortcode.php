<?php

/**
 * This file holds functions related to wordpress short codes.
 */

require_once( DEW_PREFIX . '/eventsCalendarClient.php' );
require_once( DEW_PREFIX . '/dew_tools.php' );
require_once( DEW_PREFIX . '/dew_format.php' );

function dew_calendar_shortcode_handler ($atts, $content = null, $code = "") {
	
}

function dew_detailbox_shortcode_handler ($atts, $content = null, $code = "") {
	/**
	 * Requires that the type attribute is set
	 * type can be event or festival
	 */

	$validTypes = array('event', 'festival');

	if (!isset($atts['type']) || !in_array($atts['type'], $validTypes)) {
		return "<p>You must use [dew_detailBox type=&lt;type&gt; id=&lt;id&gt;], with type being either event or festival.</p>";
	}

	$type = $atts['type'];

	$options = get_option('optionsDakEventsWp');
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	$queryArgs = array();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	if (!isset($atts['id'])) {
		return "<p>" . __('No id specified for detailbox', 'dak_events_wp') . "</p>";
	}
	
	if ($type == 'event') {
		$result = $client->event($atts['id']);
	} else if ($type == 'festival') {
		$result = $client->festival($atts['id']);
	}

	if ($result->totalCount == 0) {
		return "<p>" . sprintf(__('No event found with specified id %d.', 'dak_events_wp'), $atts['id']) . "</p>";
	}

	$arr = $result->data[0];

	$startTimestamp = DEW_tools::dateStringToTime($arr->startDate, $arr->startTime);
	$endTimestamp = DEW_tools::dateStringToTime($arr->endDate, $arr->endTime);

	$location = DEW_tools::getLocationFromEvent($arr);

	if ($type == 'event') {
		$categories = '';
		foreach ($arr->categories as $c) {
			$categories .= $c->name . ', ';
		}
		$categories = substr($categories, 0, -2);
	}

	if ($type == 'festival') {
		$arrangers = '';
		foreach ($arr->arrangers as $a) {
			$arrangers .= $a->name . ', ';
		}
		$arrangers = substr($arrangers, 0, -2);
	}

	$extra = "";

	if (strlen($arr->covercharge) > 0) {
		$extra .= __('CC:', 'dak_events_wp') . ' ' . $arr->covercharge . '<br />' . "\n";
	}

	$startDayName = ucfirst(date_i18n('l', $startTimestamp));
	$dayInMonth = date('j', $startTimestamp);
	$monthName = date_i18n('F', $startTimestamp);

	$title = '<a href="' . DEW_tools::generateLinkToArrangement($arr, $type) . '">' . $arr->title . '</a>';

	$renderArr = array(
			'title' => $title,
			'startDayName' => $startDayName,
			'dayInMonth' => $dayInMonth,
			'monthName' => $monthName,
			'startTime' => date('H:i', $startTimestamp),
			'location' => $location,
			'iCalUrl' => $arr->ical,
			'googleCalUrl' => DEW_tools::createGoogleCalUrl($arr),
			'extra' => $extra,
	);

	if ($type == 'event') {
		$output =  DEW_tools::sprintfn(DEW_format::eventDetailBox(), $renderArr + array(
			'arranger' => $arr->arranger->name,
			'category' => $categories,
		));
	} else if ($type == 'festival') {
		$output =  DEW_tools::sprintfn(DEW_format::festivalDetailBox(), $renderArr + array(
			'endDatetime' => ($arr->startDate == $arr->endDate) ? date('H:i', $endTimestamp) : date_i18n(__('F j, Y H:i'), $endTimestamp),
			'arranger' => $arrangers,
		));
	}

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
	 *       'festival_id' => '1,2,3,2',
	 *       'no_current_events' => 1 or 0,
	 *       'semester_view' => 1 or 0,
	 * )
	 */
	$options = get_option('optionsDakEventsWp');
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	$queryArgs = array();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	//print_r($atts);
	
	$queryArgs['onlySummaries'] = 1;

	$atts = str_replace(array("\"", "'", "&quot;"), array('', '', ''), $atts);

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
		
	if ( ! empty($atts['semester_view']) && ($atts['semester_view'] == 1) ) {
		$thisMonth = intval(date('n'));

		if ( $thisMonth <= 7 ) {
			$agendaTitle = '<h2>' . sprintf(__('Events in spring %s', 'dak_events_wp'), date('Y')) . '</h2>' . "\n"; 
			$startDate = date('Y') . '-01-01';
			$endDate = date('Y') . '-07-31';
		} else {
			$agendaTitle = '<h2>' . sprintf(__('Events in autumn %s', 'dak_events_wp'), date('Y')) . '</h2>' . "\n"; 
			$startDate = date('Y') . '-08-01';
			$endDate = (intval(date('Y')) + 1) . '-01-31';
		}
		
		$queryArgs['startDate'] = $startDate;
		$queryArgs['endDate'] = $endDate;
		
		$results = $client->filteredEventsList($queryArgs, true);
		$eventFormat = DEW_format::agendaCompactList();
		$eventCollectionFormat = DEW_format::agendaEventCollection();
	} else {
		$results = $client->filteredEventsList($queryArgs);
		$eventFormat = DEW_format::agendaFullEvent();
	}

	$dateSortedEvents = DEW_tools::groupEventsByDate($results->data);

	$eventDateCollectionFormat = DEW_format::agendaEventDateCollection();
	
	$output = "";

	if (!empty($agendaTitle)) {
		$output .= $agendaTitle;
	}
	
	$output .= "<div class='dew_agenda'>\n";

	$monthOutput = "";

	$lastMonth = 0;

	$dateSortedEventsKeys = array_keys($dateSortedEvents);
	$numberOfDateSortedEvents = count($dateSortedEventsKeys);
	for ($i = 0; $i < $numberOfDateSortedEvents; $i++) {
		$timestamp = $dateSortedEventsKeys[$i];
		$events = $dateSortedEvents[$timestamp];

		$startDayName = ucfirst($locale->get_weekday(date('w', $timestamp )));
		$monthName = ucfirst($locale->get_month(date('n', $timestamp )));

		$dateOutput = "";

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

			$extra = "";

			if (strlen($event->covercharge) > 0) {
				$extra .= __('CC:', 'dak_events_wp') . ' ' . $event->covercharge . '<br />' . "\n";
			}

			$dateOutput .= DEW_tools::sprintfn($eventFormat, array(
				'title' => $event->title,
				'leadParagraph' => $event->leadParagraph,
				'renderedDate' => $renderedDate,
				'location' => $location,
				'arranger' => $event->arranger->name,
				'category' => $categories,
				'startTime' => date($timeFormat, $startTimestamp),
				'readMore' => DEW_tools::generateLinkToArrangement($event, 'event'),
				'extra' => $extra,
			));

		}

		$monthOutput .= DEW_tools::sprintfn($eventDateCollectionFormat,
			array(
				'dayName' => $startDayName,
				'dayNumber' => date('j', $timestamp),
				'monthName' => $monthName,
				'eventCollection' => $dateOutput,
			)
		
		);
		
		if ( ! empty($atts['semester_view']) && ($atts['semester_view'] == 1) ) {
			if ( (($i + 1) == $numberOfDateSortedEvents) || (date('n', $timestamp) != date('n', $dateSortedEventsKeys[$i + 1])) ) {
				$output .= DEW_tools::sprintfn($eventCollectionFormat, array(
					'monthName' => $monthName,
					'id' => 'm' . date('n', $timestamp),
					'extraClass' => ((date('n') != date('n', $timestamp)) ? 'dew_hide' : ''),
					'extraCollectionClass' => ((date('n') == date('n', $timestamp)) ? 'dew_active' : ''),
					'eventCollection' => $monthOutput,
				));

				$monthOutput = "";
			}
		} else {
			$output .= $monthOutput;
		}
	}

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

	$formatConfig = array();

	if (isset($atts['no_title']) && ($atts['no_title'] == true)) {
		$formatConfig['no_title'] = true;
	}

	$eventFormat = DEW_format::fullEvent($formatConfig);

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

	$extra = "";

	if (strlen($event->covercharge) > 0) {
		$extra .= __('CC:', 'dak_events_wp') . ' ' . $event->covercharge . '<br />' . "\n";
	}

	if ($event->festival != null) {
		$extra .= __('Part of festival', 'dak_events_wp') . ': ' 
		       . '<a href="' . DEW_tools::generateLinkToArrangement($event->festival, 'festival') . '">' . $event->festival->title .'</a><br />';
	}

	$output = DEW_tools::sprintfn($eventFormat, array(
		'title' => $event->title,
		'leadParagraph' => DEW_tools::allowedHtml($event->leadParagraph),
		'description' => DEW_tools::allowedHtml($event->description),
		'renderedDate' => $renderedDate,
		'location' => $location,
		'arranger' => $event->arranger->name,
		'category' => $categories,
		'startTime' => date($timeFormat, $startTimestamp),
		'urlOriginal' => $event->url,
		'iCalUrl' => $event->ical,
		'googleCalUrl' => DEW_tools::createGoogleCalUrl($event),
		'extra' => $extra,
	));

	return $output;
}

function dew_fullfestival_shortcode_handler ($atts, $content = null, $code = "") {
	/**
	 * $atts can contain
	 * array(
	 *       'festival_id' => '1',
	 * )
	 */

	$options = get_option('optionsDakEventsWp');

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	$festivalResult = $client->festival($atts['festival_id']);
	$festival = $festivalResult->data[0];

	$formatConfig = array();

	if (isset($atts['no_title']) && ($atts['no_title'] == true)) {
		$formatConfig['no_title'] = true;
	}

	$festivalFormat = DEW_format::fullFestival($formatConfig);

	//var_dump($festival);

	$startTimestamp = DEW_tools::dateStringToTime($festival->startDate, $festival->startTime);
	$endTimestamp = DEW_tools::dateStringToTime($festival->endDate, $festival->endTime);

	if ($festival->startDate == $festival->endDate) {
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

	$location = DEW_tools::getLocationFromEvent($festival);

	$arrangers = '';
	foreach ($festival->arrangers as $f) {
		$arrangers .= $f->name . ', ';
	}
	$arrangers = substr($arrangers, 0, -2);

	$extra = "";

	if (strlen($festival->covercharge) > 0) {
		$extra .= __('CC:', 'dak_events_wp') . ' ' . $festival->covercharge . '<br />' . "\n";
	}

	$output = DEW_tools::sprintfn($festivalFormat, array(
		'title' => $festival->title,
		'leadParagraph' => DEW_tools::allowedHtml($festival->leadParagraph),
		'description' => DEW_tools::allowedHtml($festival->description),
		'renderedDate' => $renderedDate,
		'location' => $location,
		'arranger' => $arrangers,
		'startTime' => date($timeFormat, $startTimestamp),
		'urlOriginal' => $festival->url,
		'iCalUrl' => $festival->ical,
		'googleCalUrl' => DEW_tools::createGoogleCalUrl($festival),
		'extra' => $extra,
		'festivalEvents' => dew_agenda_shortcode_handler(array('festival_id' => $festival->id, 'no_current_events' => true)),
	));

	return $output;
}

function dew_agenda_or_fullarrangement_shortcode_handler ($atts, $content = null, $code = "") {
	global $wp_query;

	if (!empty($_GET['event_id']) || $wp_query->get('event_id')) {
		$event_id = (empty($_GET['event_id'])) ? $wp_query->get('event_id') : $_GET['event_id'];
		return dew_fullevent_shortcode_handler (array('event_id' => intval($event_id)), $content, $code);
	} elseif (!empty($_GET['festival_id']) || $wp_query->get('festival_id')) {
		$festival_id = (empty($_GET['festival_id'])) ? $wp_query->get('festival_id') : $_GET['festival_sid'];
		return dew_fullfestival_shortcode_handler (array('festival_id' => intval($festival_id)), $content, $code);
	} else {
		return dew_agenda_shortcode_handler ($atts, $content, $code);
	}
}
