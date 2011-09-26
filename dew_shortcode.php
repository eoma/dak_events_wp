<?php

/**
 * This file holds functions related to wordpress short codes.
 */

require_once( DEW_PREFIX . '/eventsCalendarClient.php' );
require_once( DEW_PREFIX . '/dew_tools.php' );
require_once( DEW_PREFIX . '/dew_format.php' );

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
	 * Requires that the type attribute is set
	 * type can be event or festival
	 */

	$validTypes = array('event', 'festival');

	if (!isset($atts['type']) || !in_array($atts['type'], $validTypes)) {
		return "<p>You must use [dew_detailBox type=&lt;type&gt; id=&lt;id&gt;], with type being either event or festival.</p>";
	}

	$type = $atts['type'];

	$options = DEW_Management::getOptions();
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
	 *       'title' => '<h1>Events in may</h1>',
	 * )
	 */
	$options = DEW_Management::getOptions();
	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	$queryArgs = array();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	//print_r($atts);
	
	$queryArgs['onlySummaries'] = 1;

	$atts = str_replace(array("\"", "'", "&quot;"), array('', '', ''), $atts);
	$title = null;

	if ( ! empty($atts['title']) )
		$title = trim($atts['title']);

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

    if ( ! empty($atts['start_date']) ) {
		$queryArgs['startDate'] = strval($atts['start_date']);
	}

    if ( ! empty($atts['end_date']) ) {
		$queryArgs['endDate'] = strval($atts['end_date']);
	}

    if ( ! empty($atts['dayspan']) ) {
		$queryArgs['dayspan'] = intval($atts['dayspan']);
	}

	if ( (isset($queryArgs['start_date']) && isset($queryArgs['end_date'])) || isset($queryArgs['dayspan']) ) {
		$results = $client->filteredEventsList($queryArgs, true);
	} else {
		$results = $client->filteredEventsList($queryArgs);
	}

	if( ! empty($atts['compact_view']) && ($atts['compact_view'] == 1) ) {
		$eventFormat = DEW_format::agendaCompactEvent();
	} else {
		$eventFormat = DEW_format::agendaFullEvent();
	}

	$eventDateCollectionFormat = DEW_format::agendaEventDateCollection();

	$dateSortedEvents = DEW_tools::groupEventsByDate($results->data, $options['dayStartHour']);
	
	$output = "";

	if (!empty($title)) {
		$output .= $title;
	}
	
	$output .= "<div class='dew_agenda'>\n";

	$dateSortedEventsKeys = array_keys($dateSortedEvents);
	$numberOfDateSortedEvents = count($dateSortedEventsKeys);

	if ($results->count == 0) {
		$output .= "<p>No events found :(</p>\n";
	}

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

		$output .= DEW_tools::sprintfn($eventDateCollectionFormat,
			array(
				'dayName' => $startDayName,
				'dayNumber' => date('j', $timestamp),
				'monthName' => $monthName,
				'eventCollection' => $dateOutput,
			)
		
		);
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

	$primaryPicture = '';

	if ($options['eventUsePictures'] && !empty($event->primaryPicture)) {
		$img = DEW_tools::getPicture($event->primaryPicture, $options['eventPictureWidth']);

		if ($img != false) {
			$primaryPicture = '<img class="' . $options['eventPictureClass'] . ' size-auto" alt="' . esc_attr($event->primaryPicture->description) . '" src="' . content_url('uploads' . $img['relative']) . '" />';
		}
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
		'primaryPicture' => $primaryPicture,
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

	$options = DEW_Management::getOptions();

	$dateFormat = $options['dateFormat'];
	$timeFormat = $options['timeFormat'];

	$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
	$locale = new WP_Locale();

	if (empty($atts['festival_id'])) {
		return __('No festival_id attribute supplied to shortcode [dew_festival]', 'dak_events_wp');
	}

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

	$locale = new WP_Locale();

	$content = "<ul class=\"agenda_menu\">\n";
		
	$dew_archive = null;

	if (!empty($_GET['dew_archive'])) {
		$dew_archive = strval($_GET['dew_archive']);
	}

	$class = '';
	if (empty($dew_archive)) {
		$class = 'class="active"';
	}

	$content .= '<li ' . $class . '><a href="' . get_permalink() . '">Next ' . $atts['dayspan'] . ' days</a></li>' . "\n";

	$currentMonth = intval(date('n'));
	$currentYear = intval(date('Y'));

	$queryYear = 0;
	$queryMonth = 0;

	$month = $currentMonth;
	$year = $currentYear;

	if (isset($_GET['dew_archive'])) {
		$dateComponents = explode('-', $_GET['dew_archive']);

		if (count($dateComponents) >= 2) {
			$queryYear = intval($dateComponents[0]);
			$queryMonth = intval($dateComponents[1]);
		}
	}

	for ($i = 0; $i < 4; $i++) {
		$monthName = ucfirst($locale->get_month($month));

		$class = '';
			
		if (($year == $queryYear) && ($month == $queryMonth)) {
			$class = 'class="active"';
		}

		$content .= '<li ' . $class . '><a href="?dew_archive=' . sprintf('%04d-%02d', $year, $month) . '">' . $monthName . '</a></li>'. "\n";

		if ($month == 12) {
			$month = 1;
			$year++;
		} else {
			$month++;
		}
	}

	$class = '';
	if ($dew_archive == 'list') {
		$class = 'class="active"';
	}
	$content .= '<li ' . $class . '><a href="?dew_archive=list">Archive</a></li>' . "\n";

	$content .= "</ul>\n";

	if (empty($dew_archive)) {

	}

	return $content;
}

/**
 * $atts can be array(
 *   'arranger_id' => '1,2,3,4',
 *   'category_id' => '1,2,3,4',
 *   'location_id' => '1,2,3,4',
 *   'exclude_menu' => 1 or 0,
 *   'dayspan' => any number bigger than or equal to 0
 * );
 */
function dew_agenda_or_arrangement_shortcode_handler ($atts, $content = null, $code = "") {
	global $wp_query;

	if (!empty($_GET['event_id']) || $wp_query->get('event_id')) {
		$event_id = (empty($_GET['event_id'])) ? $wp_query->get('event_id') : $_GET['event_id'];

		return dew_fullevent_shortcode_handler (array('event_id' => intval($event_id)), $content, $code);
	} elseif (!empty($_GET['festival_id']) || $wp_query->get('festival_id')) {
		$festival_id = (empty($_GET['festival_id'])) ? $wp_query->get('festival_id') : $_GET['festival_sid'];

		return dew_fullfestival_shortcode_handler (array('festival_id' => intval($festival_id)), $content, $code);
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

		if (isset($atts['arranger_id'])) {
			$config['arranger_id'] = $atts['arranger_id'];
		}

		if (isset($atts['category_id'])) {
			$config['category_id'] = $atts['category_id'];
		}

		if (isset($atts['location_id'])) {
			$config['location_id'] = $atts['location_id'];
		}

		$dew_archive = null;

		if (!empty($_GET['dew_archive'])) {
			$dew_archive = strval($_GET['dew_archive']);
		}

		if (!isset($atts['exclude_menu']) || ($atts['exclude_menu'] == 0)) {
			$content .= dew_agenda_menu_shortcode_handler(array('dayspan' => $atts['dayspan']));
		}

		$currentMonth = intval(date('n'));
		$currentYear = intval(date('Y'));

		$queryYear = 0;
		$queryMonth = 0;

		if (isset($_GET['dew_archive'])) {
			$dateComponents = explode('-', $_GET['dew_archive']);

			if (count($dateComponents) >= 2) {
				$queryYear = intval($dateComponents[0]);
				$queryMonth = intval($dateComponents[1]);
			}

			if (($queryMonth >= 1) && ($queryMonth <= 12)) {
				$config['start_date'] = sprintf('%04d-%02d-01', $queryYear, $queryMonth);
				$config['end_date'] = sprintf('%04d-%02d-', $queryYear, $queryMonth);

				if (in_array($queryMonth, array(1, 3, 5, 7, 8, 10, 12))) {
					$config['end_date'] .= '31';
				} else if (in_array($queryMonth, array(4, 6, 9, 11))) {
					$config['end_date'] .= '30';
				} else {
					if (date('L', strtotime($queryYear . '-01-01'))) {
						$config['end_date'] .= '29';
					} else {
						$config['end_date'] .= '28';
					}
				}
			}
		}

		if ($dew_archive == 'list') {
			$options = DEW_Management::getOptions();
			$client = new eventsCalendarClient ($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);

			$historyList = $client->filteredHistoryList();

			$content .= '<ul class="agenda_archive">' . "\n";

			$previousYear = null;
			$yearElements = null;

			foreach (array_reverse($historyList->data) as $h) {
				$ts = strtotime($h->date);
				$year = date('Y', $ts);
				$month = date('n', $ts);

				if ($year != $previousYear) {
					
					if ( ! empty($yearElements) ) {
						$content .= ' <li><span class="agenda_archive_year">' . $previousYear . '</span>' . "\n";
						$content .= '  <ul>' . "\n";
						$content .= $yearElements . "\n";
						$content .= '  </ul>' . "\n";
						$content .= ' </li>' . "\n";
					}
					
					$previousYear = $year;
					$yearElements = '';
				}

				$yearElements .= '   <li><a href="?dew_archive=' . date('Y-m', $ts) . '">' . $locale->get_month($month) . '</a></li>' . "\n";
			}

			$content .= ' <li><span class="agenda_archive_year">' . $previousYear . '</span>' . "\n";
			$content .= '  <ul>' . "\n";
			$content .= $yearElements . "\n";
			$content .= '  </ul>' . "\n";
			$content .= ' </li>' . "\n";

			$content .= '</ul>' . "\n";
		} else {
			
			if (!empty($dew_archive)) {
				$config['title'] = '<h2 class="agenda_title">Events in ' . $locale->get_month($queryMonth) . ' ' . $queryYear . '</h2>' . "\n";
			} else {
				$config['title'] = '<h2 class="agenda_title">Events for the next ' . $atts['dayspan'] . ' days.</h2>';
				$config['dayspan'] = $atts['dayspan'];
			}
			
			$content .= dew_agenda_shortcode_handler ($config);
		}

		return $content;
	}
}
