<?php

/**
 * This file contains a class containing useful tools
 */

class DEW_tools {

	/**
	 * Will return associative array where events are sorted by start date
	 * Index key will be timestamp of date. The value related to the key will be
	 * the object in $eventList
	 * 
	 * @param array $eventList An event list
	 * @return array
	 */
	static public function groupEventsByDate (array $eventList) {
		$dateArray = array();

		foreach ($eventList as $event) {
			list($year, $month, $day) = explode('-', $event->startDate);
			$startTimestamp = mktime(0, 0, 0, $month, $day, $year);

			$dateArray[ strval($startTimestamp) ][] = $event;
		}

		return $dateArray;
	}
}
