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
	static public function groupEventsByDate (array $eventList, $dayStartHour = 0) {
		$dateArray = array();
		
		$dayStartHour = intval($dayStartHour);

		foreach ($eventList as $event) {
			$startTimestamp = DEW_tools::dateStringToTime($event->startDate, $event->startTime);
			
			
			$startTimestamp = $startTimestamp - $dayStartHour * 3600;
			
			$startTimestamp = strtotime(date('Y-m-d', $startTimestamp));

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

	static public function generateLinkToAgenda ($type, $dateArray = array()) {
		/**
		 * Type van be either upcoming, list or month
		 */

		global $wp_rewrite;

		if (is_null(self::$options)) {
			self::$options = DEW_Management::getOptions();
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

			if ($type == 'upcoming') {
				// Do nothing
				//$pageLink .= '';
			} else if ($type == 'list') {
				$pageLink .= 'archive/';
			} else if ($type == 'month') {
				if (count($dateArray) >= 2) {
					$pageLink .= 'archive/' . sprintf('%04d/%02d', $dateArray[0], $dateArray[1]) . '/';
				}
			}
		} else {
			if (strpos($pageLink, '?') === false) {
				$pageLink .= '?';
			}

			if (strpos($pageLink, '?') < (strlen($pageLink) - 1)) {
				$pageLink .= '&amp;';
			}

			if ($type == 'upcoming') {
				// Do nothing
				//$pageLink .= '';
			} else if ($type == 'list') {
				$pageLink .= 'dew_archive=list';
			} else if ($type == 'month') {
				if (count($dateArray) >= 2) {
					$pageLink .= 'dew_archive=' . sprintf('%04d-%02d', $dateArray[0], $dateArray[1]);
				}
			}
		}

		return $pageLink;
	}

	static public function generateLinkToArrangement ($arr, $type, $useTitleInUrl = true) {
		/**
		 * Type van be either event or festival
		 */

		global $wp_rewrite;

		if (is_null(self::$options)) {
			self::$options = DEW_Management::getOptions();
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

			$pageLink .= $arr->id;
			if ($useTitleInUrl) {
				$pageLink .= '/' . sanitize_title($arr->title);
			}
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
	
	static function curl_get_content($url) {
		//Initialize the Curl session
		$ch = curl_init();
		//Set curl to return the data instead of printing it to the browser.
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		// Do not verify SSL-certificate, use with care.
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		//Set the URL
		curl_setopt($ch, CURLOPT_URL, $url);

		//Execute the fetch
		$data = curl_exec($ch);

		//Close the connection
		curl_close($ch);

		return $data;
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

		return $base . '?' . http_build_query($query, '', '&amp;');
	}

	/**
	 * Will return array of absolute and relative path of transformed picture
	 * It should only use the image functions avaiable in wordpress core.
	 *
	 * @param $picObj object     Object containing variables called url
	 * @param $maxWidth integer  Maximum width of the image
	 * @return mixed
	 */
	static function getPicture ($picObj, $maxWidth = 600, $maxHeight = 1000) {

		// $picObj should be of the type that comes with 
		// event object (eg. event->primaryPicture or event->pictures[...]) or festival object

		$uploadDir = wp_upload_dir();

		$md5path = md5($picObj->url);
		$pathdata = pathinfo($picObj->filename);

		// We first construct its path
		$relativeFilePath = '/dew_pictures/' . substr($md5path, 0, 2)
		                  . '/' . substr($md5path, 2, 2)
		                  . '/' . $md5path . '-' . $maxWidth . 'x' . $maxHeight . '.' . $pathdata['extension'];

		$filePath = $uploadDir['basedir'] . $relativeFilePath;

		$imageData = array(
			'relative' => $relativeFilePath,
			'absolute' => $filePath,
		);

		if ( ! file_exists($filePath) ) {
			$tmpFile = tempnam(sys_get_temp_dir(), rand(1000,9999)) . '.' . $pathdata['extension'];
			
			if (function_exists('curl_init')) {
				$content = DEW_tools::curl_get_content($picObj->url);
			} else {
				$content = file_get_contents($picObj->url);
			}

			$gotFile = file_put_contents($tmpFile, $content);

			if ( $gotFile === false ) {
				if (WP_DEBUG) echo "Could not save picture to temporary location\n";
				return false;
			}

			if (!file_exists(dirname($filePath))) {
				wp_mkdir_p(dirname($filePath));
			}

			if ($picObj->width > $maxWidth || $picObj->height > $maxHeight) {
				$result = image_resize($tmpFile, $maxWidth, $maxHeight);
			
				unlink ($tmpFile);
			} else {
				$result = $tmpFile;
			}
			
			if ( is_wp_error($result) ) {
				if (WP_DEBUG) echo $result->get_error_message();
				return False;
			}

			rename ($result, $filePath);

			clearstatcache();

			// Set correct file permissions
			$stat = @ stat( dirname( $filePath ) );
			$perms = $stat['mode'] & 0007777;
			$perms = $perms & 0000666;
			@ chmod( $filePath, $perms );
			@ chgrp( $filePath, $stat['gid'] );
			clearstatcache();

		}

		return $imageData;
	}

	/**
	 * Will recursively remove files and directories, including $path if it's a directory
	 */
	public static function rrmdir($path) {
		// Taken from http://www.php.net/manual/en/function.unlink.php#100092
		if (file_exists($path)) {
			if (is_file($path)) {
				return  @unlink($path);
			} else {
				return array_map(array('DEW_tools', 'rrmdir'), glob($path.'/*')) == @rmdir($path);
			}
		} else {
			return false;
		}
	}
}
