<?php
if(!class_exists("DEW_Calendar")) :
require_once(DEW_PREFIX . '/eventsCalendarClient.php');

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
		$format = $options['dateFormat'];
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

		$output = '<ul id="' . $id_base . '-dak-events-wp-list">';

		//var_dump($events);

		foreach($events->data as $event) {
			$splitDate = explode("-", $event->startDate);
			$month = $splitDate[1];
			$day = $splitDate[2];
			$year = $splitDate[0];
			if ($event->startTime != '') {
				$splitTime = explode(':', $event->startTime);
				$hour = $splitTime[0];
				$min = $splitTime[1];
			} else {
				$hour = '00';
				$min = '00';
			}
			
			$startTimeStp = mktime(0, $min,  $hour, $month, $day, $year);
			
			$splitDate = explode("-", $event->endDate);
			$month = $splitDate[1];
			$day = $splitDate[2];
			$year = $splitDate[0];
			if ($event->endTime != '') {
				$splitTime = explode(':', $event->endTime);
				$hour = $splitTime[0];
				$min = $splitTime[1];
			} else {
				$hour = '00';
				$min = '00';
			}
			
			$endTimeStp = mktime(0, $min,  $hour, $month, $day, $year);
			
			$startDate = date("$format", $startTimeStp );
			$startDayName = ucfirst($this->locale->get_weekday(date('w', $startTimeStp )));
			
			$endDate = date("$format", $endTimeStp );
			$endDayName = ucfirst($this->locale->get_weekday(date('w', $startTimeStp )));
				
			$titlinked = '<strong>' . $startDayName . ' ' . $startDate . '</strong>: ' . $event->title;

			// don't send T\'itles 
			if (false !== strpos($titlinked, "\'"))
				$titlinked = stripslashes($titlinked);

			//$startDate = $startDate < date("$format") ? date("$format") : $startDate;
			$output .= '<li id="' . $id_base . '-dak-events-wp-list-' . $event->id . '">';
			$output .= '<div class="dew_showEvent">' . $titlinked . '</div>';
			
			$output .= '<div class="dew_eventElem dew_hide">';
			
			$output .= '<div class="dew_content">' . $event->leadParagraph . '</div>';
			$output .= '<div class="dew_data">';
			$output .= '<strong>Starts</strong> ' . $startDayName .' ' . $startDate . ' ' . date($timeFormat, $startTimeStp) . '<br />';
			$output .= '<strong>Ends</strong> ' . $endDayName .' ' . $endDate . ' ' . date($timeFormat, $endTimeStp) . '<br />';
			$output .= '<strong>Where?</strong> ';
			if ($event->location_id > 0) {
				$output .= $event->recurringLocation->name;
			} else {
				$output .= $event->customLocation;
			}
			$output .= '<br />';
			$output .= '<strong>Who?</strong> ' . $event->arranger->name . '<br />';
			$output .= '<strong>What?</strong> ' . $event->category->name . '<br />';
			$output .= '</div>';
			
			$output .= '</div>';
			
			$output .= '</li>' . "\n";
		}

		$output .= "</ul>";
		
		if ($output == '<ul id="' . $id_base . '-dak-events-wp-list"></ul>') {
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
