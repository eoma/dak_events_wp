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

	/**
	 * Constructor.
	 */
	function __construct() {
		$options = get_option('optionsDakEventsWp');
		$this->eventServerUrl = $options['eventServerUrl'];
		$this->locale = new WP_Locale();
	}
	
	/**
	 * Displays the Event List Widget.
	 *
	 * @param int $num   number of events to list
	 */
	function displayEventList($num, $filter = null, $id_base = null) {
		$client = new eventsCalendarClient($this->eventServerUrl);
		
		$options = get_option('optionsDakEventsWp');
		$dateFormat = $options['dateFormat'];
		$timeFormat = $options['timeFormat'];

		if ( isset($filter) && is_array($filter) 
		     && (!empty($filter['arranger_id']) || !empty($filter['location_id']) || !empty($filter['category_id'])) ) {
			if (isset($filter['endDate'])) unset($filter['endDate']);
			if (isset($filter['startDate'])) unset($filter['startDate']);
			$filter['startDate'] = date('Y-m-d');
			//$filter['endDate'] = date('Y-m-d');
			$events = $client->filteredEventsList($filter, $num);
		} else {
			$events = $client->upcomingEvents($num);
		}

		$output = '<ul class="dew_eventList" id="' . $id_base . '-dak-events-wp-list">';

		$eventFormat = DEW_format::eventInList();

		$startDateTimestamp = 0;
		foreach($events->data as $event) {
			$startDateTimestampTmp = DEW_tools::dateStringToTime($event->startDate);

			$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);
			$endTimestamp = DEW_tools::dateStringToTime($event->endDate, $event->endTime);

			$startDayName = ucfirst($this->locale->get_weekday(date('w', $startTimestamp )));
			$endDayName = ucfirst($this->locale->get_weekday(date('w', $endTimestamp )));
						
			if ($startDateTimestamp != $startDateTimestampTmp) {
				$startDateTimestamp = $startDateTimestampTmp;
				$output .= '<li class="dew_eventList_date"><strong>' . $startDayName . ' ' . date($dateFormat, $startTimestamp) . '</strong></li>' ;
			}

			$location = DEW_tools::getLocationFromEvent($event);

			if ($event->startDate == $event->endDate) {
				$renderedDate = $startDayName . ' ' . date($dateFormat, $startTimestamp)
				              . ' from ' . date($timeFormat, $startTimestamp) . ' to '
				              . date($timeFormat, $endTimestamp);
			} else {
				$renderedDate = $startDayName . ' ' . date($dateFormat . ' ' . $timeFormat, $startTimestamp) . ' to '
				              . $endDayName . ' ' . date($dateFormat . ' ' . $timeFormat, $endTimestamp);
			}

			$output .= '<li class="dew_event" id="' . $id_base . '-dak-events-wp-list-' . $event->id . '">';

			$output .= DEW_tools::sprintfn($eventFormat, array(
				'title' => $event->title,
				'leadParagraph' => $event->leadParagraph,
				'renderedDate' => $renderedDate,
				'location' => $location,
				'arranger' => $event->arranger->name,
				'category' => $event->category->name,
			));

			$output .= '</li>' . "\n";
		}

		$output .= "</ul>";
		
		if ($output == '<ul class="dew_eventList" id="' . $id_base . '-dak-events-wp-list"></ul>') {
			echo '<ul><li id="no-events-in-list"><strong>Events are coming soon, stay tuned!</strong></li></ul>' ."\n";
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
