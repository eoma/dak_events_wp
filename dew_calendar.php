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

		$dateSortedEvents = DEW_tools::groupEventsByDate($events->data, $this->options['dayStartHour']);

		ob_start();

		do_action('dew_render_widget_list', $dateSortedEvents, array('dateFormat' => $dateFormat, 'id_base' => $id_base));

		echo ob_get_clean();
	}
}
endif;
?>
