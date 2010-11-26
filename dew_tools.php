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

	static public function stringToIntArray($string, $separator = ',') {
		$array = explode($separator, $string);
		return array_map(create_function('$value', 'return (int)$value;'), $array);
	}

	static public function dateStringToTime ($stringDate, $stringTime = '') {
		$dateArr = explode('-', $stringDate);
		list($year, $month, $day) = $dateArr;

		$timeArr = ( ! empty($stringTime) ) ? explode(':', $stringTime) : array();
		if (count($timeArr) == 3) {
			list($hour, $minute, $second) = $timeArr;
		} else {
			$hour = 0;
			$minute = 0;
			$second = 0;
		}

		return mktime($hour, $minute, $second, $month, $day, $year);
	}

	static public function getLocationFromEvent(stdClass $event) {
		if (empty($event->location_id) || ($event->location_id == 0)) {
			return $event->customLocation;
		} else {
			return $event->recurringLocation->name;
		}
	}
}
