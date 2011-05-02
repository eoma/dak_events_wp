<?php

/**
 * This file contains a class containing useful tools
 */

class DEW_tools {

	static private $options = null;

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
			return $event->commonLocation->name;
		}
	}

	static public function generateLinkToArrangement ($arr, $type) {
		/**
		 * Type van be either event or festival
		 */

		global $wp_rewrite;

		if (is_null(self::$options)) {
			self::$options = get_option('optionsDakEventsWp');
		}

		if (self::$options['eventPageId'] <= 0) {
			// Don't construct any links if it's not an existing page
			return $arr->url;
		}

		$pageLink = get_page_link(self::$options['eventPageId']);

		if ($wp_rewrite->using_permalinks()) {

			if (strrpos($pageLink, '/') < (strlen($pageLink) - 1)) {
				// Append forwardslash if it's not the at end of the link
				$pageLink .= '/';
			}

			if ($type == 'festival') {
				$pageLink .= 'festival/';
			}

			$pageLink .= $arr->id . '/' . sanitize_title($arr->title);
		} else {
			if (strpos($pageLink, '?') === false) {
				$pageLink .= '?';
			}

			if (strpos($pageLink, '?') < (strlen($pageLink) - 1)) {
				$pageLink .= '&amp;';
			}

			if ($type == 'event') {
				$pageLink .= 'event_id=' . $arr->id;
			} else if ($type == 'festival') {
				$pageLink .= 'festival_id=' . $arr->id;
			}
		}

		return $pageLink;
	}

	/**
	 * taken from http://www.php.net/manual/en/function.sprintf.php#94608
	 * version of sprintf for cases where named arguments are desired (python syntax)
	 *
	 * with sprintf: sprintf('second: %2$s ; first: %1$s', '1st', '2nd');
	 *
	 * with sprintfn: sprintfn('second: %(second)s ; first: %(first)s', array(
	 *  'first' => '1st',
	 *  'second'=> '2nd'
	 * ));
	 *
	 * @param string $format sprintf format string, with any number of named arguments
	 * @param array $args array of [ 'arg_name' => 'arg value', ... ] replacements to be made
	 * @return string|false result of sprintf call, or bool false on error
	 */
	static public function sprintfn ($format, array $args = array()) {
		// map of argument names to their corresponding sprintf numeric argument value
		$arg_nums = array_slice(array_flip(array_keys(array(0 => 0) + $args)), 1);

		// find the next named argument. each search starts at the end of the previous replacement.
		for ($pos = 0; preg_match('/(?<=%)\(([a-zA-Z_]\w*)\)/', $format, $match, PREG_OFFSET_CAPTURE, $pos);) {
			$arg_pos = $match[0][1];
        		$arg_len = strlen($match[0][0]);
			$arg_key = $match[1][0];

			// programmer did not supply a value for the named argument found in the format string
			if (! array_key_exists($arg_key, $arg_nums)) {
				user_error("sprintfn(): Missing argument '${arg_key}'", E_USER_WARNING);
				return false;
			}

			// replace the named argument with the corresponding numeric one
			$format = substr_replace($format, $replace = $arg_nums[$arg_key] . '$', $arg_pos, $arg_len);
			$pos = $arg_pos + strlen($replace); // skip to end of replacement for next iteration
		}

		return vsprintf($format, array_values($args));
	}

	static function allowedHtml ($dirtyHtml) {
		
		$allowedHtml = array(
			'a' => array(
				'href' => array(),
			),
			'p' => array(),
			'span' => array(),
			'b' => array(),
			'strong' => array(),
			'em' => array(),
			'i' => array(),
			'blockquote' => array(),
			'ul' => array(),
			'ol' => array(),
			'li' => array(),
			'br' => array(),
		);

		return wp_kses($dirtyHtml, $allowedHtml);
	}

	static function createGoogleCalUrl($arr, $linkBack = '') {
		$base = 'http://www.google.com/calendar/event';

		$linkBack = trim(strval($linkBack));

		if (empty($linkBack)) {
			$protocol = (empty($_SERVER['HTTPS']) ? 'http' : 'https');
			$linkBack = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		}

		$query = array();

		$query['action'] = 'TEMPLATE';
        $query['text'] = $arr->title;
		$query['location'] = self::getLocationFromEvent($arr);

		$startTimestamp = self::dateStringToTime($arr->startDate, $arr->startTime);
		$endTimestamp = self::dateStringToTime($arr->endDate, $arr->endTime);

		$query['dates'] = date('Ymd\THis\Z', $startTimestamp) . '/' . date('Ymd\THis\Z', $endTimestamp);

		$query['details'] = $linkBack . ': ' . strip_tags($arr->leadParagraph);

		$query['sprop'] = array();
		$query['sprop'][] = $linkBack;
		$query['sprop'][] = 'name:' . $arr->title;

		return $base . '?' . http_build_query($query);
	}
}
