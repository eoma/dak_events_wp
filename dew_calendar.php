<?php
if(!class_exists("DEW_Calendar")) :
require_once(DEW_PREFIX . '/eventsCalendarClient.php');
require_once(DEW_PREFIX . '/dew_tools.php');
require_once(DEW_PREFIX . '/dew_format.php');

/**
 * Displays the events list and the calendars
 */
class DEW_Calendar {

	/**
	 * Holds the WP_Locale object.
	 * @var object
	 * @access private
	 */
	private $locale;

	private $eventServerUrl;
	private $options;

	/**
	 * Constructor.
	 */
	function __construct() {
		$this->options = DEW_Management::getOptions();
		$this->eventServerUrl = $this->options['eventServerUrl'];
		$this->locale = new WP_Locale();
	}
	
	/**
	 * Displays the Event List Widget.
	 *
	 * @param int $num   number of events to list
	 */
	function displayEventList($num, $filter = null, $id_base = null) {
		$client = new eventsCalendarClient($this->eventServerUrl, null, $this->options['cache'], $this->options['cacheTime']);

		$dateFormat = $this->options['dateFormat'];
		$timeFormat = $this->options['timeFormat'];

		if ( isset($filter) && is_array($filter) 
		     && (!empty($filter['arranger_id']) || !empty($filter['location_id']) || !empty($filter['category_id'])) || !empty($filter['daysInFuture'])) {
			if (isset($filter['endDate'])) unset($filter['endDate']);
			if (isset($filter['startDate'])) unset($filter['startDate']);
			// If no startDate specified, events will be selected that start or ends on the current date
			//$filter['startDate'] = date('Y-m-d');
			if (isset($filter['daysInFuture']) && ($filter['daysInFuture'] > 0)) {
				$filter['dayspan'] = intval($filter['daysInFuture']);
			}
			$events = $client->filteredEventsList($filter + array('limit' => $num));
		} else {
			$events = $client->upcomingEvents($num);
		}

		$output = '<ul class="dew_eventList" id="' . $id_base . '-dak-events-wp-list">';
		
		$eventFormat = DEW_format::eventInList();

		$startDateTimestamp = 0;
		foreach($events->data as $event) {
			$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);

			// Allow events that start early next day (eg. dj's at 01:00)
			// to be listed on the former day
			$startDateTimestampTmp = strtotime(date('Y-m-d', $startTimestamp - (intval($this->options['dayStartHour']) * 3600)));
			
			//echo $event->startDate . "\n";
			//echo date('Y-m-d', $startTimestamp - (intval($this->options['dayStartHour']) * 3600)) . "\n";
			
			$endTimestamp = DEW_tools::dateStringToTime($event->endDate, $event->endTime);

			$startDayName = ucfirst($this->locale->get_weekday(date('w', $startTimestamp )));
			$endDayName = ucfirst($this->locale->get_weekday(date('w', $endTimestamp )));
			
			if ($startDateTimestamp != $startDateTimestampTmp) {
				$startDateTimestamp = $startDateTimestampTmp;
				$output .= '<li class="dew_eventList_date">'
				        . ucfirst($this->locale->get_weekday(date('w', $startDateTimestamp )))
				        . ' ' . date($dateFormat, $startDateTimestamp)
				        . '</li>' ;
			}

			$location = DEW_tools::getLocationFromEvent($event);

			if ($event->startDate == $event->endDate) {
				$renderedDate = $startDayName . ' ' . date($dateFormat, $startTimestamp)
				              . ' fra ' . date($timeFormat, $startTimestamp) . ' til '
				              . date($timeFormat, $endTimestamp);
			} else {
				$renderedDate = $startDayName . ' ' . date($dateFormat . ' ' . $timeFormat, $startTimestamp) . ' til '
				              . strftime("%R", $endTimestamp);
			}
			$output .= '<li class="dew_event" id="' . $id_base . '-dak-events-wp-list-' . $event->id . '">';

			$categories = '';
			foreach ($event->categories as $c) {
				$categories .= $c->name . ', ';
			}
			$categories = substr($categories, 0, -2);

			$extra = "";

			if (strlen($event->covercharge) > 0) {
				$extra .= '<strong>' . __('CC:', 'dak_events_wp') . '</strong> ' . $event->covercharge . "<br />\n";
			}

			if ($event->festival_id > 0) {
				$extra .= '<a href="' . DEW_tools::generateLinkToArrangement($event->festival, 'festival') . '">Part of ' . $event->festival->title . '</a><br />';
			}

			// Adds link to either internal event or external
			$extra .= '<a href="' . DEW_tools::generateLinkToArrangement($event, 'event') .'">'. __('Read more', 'dak_events_wp') .'</a>';

			$output .= DEW_tools::sprintfn($eventFormat, array(
				'title' => $event->title,
				'leadParagraph' => $event->leadParagraph,
				'renderedDate' => $renderedDate,
				'renderedTime' => date($timeFormat, $startTimestamp),
				'location' => $location,
				'arranger' => $event->arranger->name,
				'category' => $categories,
				'extra' => $extra,
			));
			$output .= '</li>' . "\n";
		}
		$output .= '</ul>' . "\n";
		
		if ($output == '<ul class="dew_eventList" id="' . $id_base . '-dak-events-wp-list"></ul>') {
			echo '<ul><li id="no-events-in-list"><strong>' . __('No arrangements at the moment.', 'dak_events_wp') . '</strong></li></ul>' ."\n";
		} else {

			if (false !== strpos($output, "\'")) {
				$output = stripslashes($output);
			}

			echo $output . "\n";
		}
	}
}
endif;
?>
