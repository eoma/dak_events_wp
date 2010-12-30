<?php

class eventsCalendarClient {

	/**
	 * Holds url of event server
	 */
	private $url;

	/**
	 * Whether or not to enable cache (apc)
	 */
	private $enableCache;

	/**
	 * If cache is enabled, for how long should it stay?
	 */
	private $cacheTime;

	/**
	 * API key, for use when adding events remotely.
	 * Currently not in use.
	 */
	private $apiKey;

	public function __construct ($url, $apiKey = null, $enableCache = 1, $cacheTime = 5) {
		$this->url = strval($url);
		$this->apiKey = $apiKey;
		$this->cacheTime = intval($cacheTime);
		$this->enableCache = intval($enableCache);
	}

	/**
	 * This function will get the data from the server
	 * and cache it for a period, say 5 seconds.
	 * It will handle the data as json.
	 * @param string $action At what action should the query be used on
	 * @param array $arguments Arguments used in the query
	 * @param bool $rawString Whether the function should return the result via json_decode() or not (raw string)
	 * @param bool $enableCache Whether the function should use or not use cache, if cache is turned on.
	 * @return mixed
	 */
	private function getData ($action, array $arguments, $rawString = false, $enableCache = true) {
		$query_args = '?';

		// Putting together the query string
		foreach ($arguments as $k => $v) {
			if (!empty($v)) {
				if (is_array($v)) {
					$v = $this->makeCommaList($v);
				}

				$query_args .= $k . '=' . $v . '&';
			}
		}

		$query_args = substr($query_args, 0, -1);

		$urlComplete = $this->url . $action .  $query_args;

		//echo $urlComplete . "\n";

		if ($this->enableCache && $enableCache) {
			// if we've enabled the cache, we check if the key exists for this query.
			$cache_key = md5($urlComplete);
			$cache_data = apc_fetch($cache_key, $cache_success);

			if ( ! $cache_success ) {
				$cache_data = file_get_contents($urlComplete);
				apc_store($cache_key, $cache_data, $this->cacheTime);
			}
		} else {
			$cache_data = file_get_contents($urlComplete);
		}

		if ($rawString) {
			return $cache_data;
		} else {
			return json_decode($cache_data);
		}
	}

	/**
	 * This function will take an array and return it's components as
	 * a string, where each component is separated by a comma.
	 * Should primarily be used for numbers.
	 * @param array $args Array, eg array(1 ,2, 3, 4)
	 * @return string
	 */
	private function makeCommaList (array $args) {
		$list = '';

		foreach ($args as $v) {
			if (!is_object($v) && !is_array($v)) {
				// Replace all occurrenceses of commas and ampersands.
				$v = str_replace(array(',', '&'), array('', ''), $v);
				$list .= urlencode($v) . ',';
			}
		}

		if (strlen($list) > 0) {
			return substr($list, 0, -1);
		} else {
			return '';
		}
	}

	/**
	 * Returns list of arrangers
	 * @return array
	 */
	public function arrangerList () {
		$args = array();

		return $this->getData('arranger/list', $args);
	}

	/**
	 * Returns list of locations
	 * @return array
	 */
	public function locationList () {
		$args = array();

		return $this->getData('location/list', $args);
	}

	/**
	 * Returns list of locations
	 * @return array
	 */
	public function categoryList () {
		$args = array();

		return $this->getData('category/list', $args);
	}

	/**
	 * Returns list of upcoming events, default maximum 20
	 * @param integer $limit Maximum number of events to pull
	 * @return array
	 */
	public function upcomingEvents ($limit = 20) {
		return $this->getData('upcomingEvents', array('limit' => intval($limit)));
	}

	/**
	 * Return list of filtered events
	 * @param array $args Array of arguments to pass on to backend.
	 * @return array
	 */
	public function filteredEventsList (array $args) {
		return $this->getData('filteredEvents' , $args );
	}
	
	/**
	 * Returns a specific event with id $id
	 * @param integer $id Event id
	 * @param array $args Other arguments
	 * @return array
	 */
	public function event($id, array $args = array()) {
		$args['id'] = intval($id);
		return $this->getData('event/get', $args);
	}

	/**
	 * Returns a specific festival with id $id
	 * @param integer $id Festival id
	 * @param array $args Other arguments
	 * @return array
	 */
	public function festival($id, array $args = array()) {
		$args['id'] = intval($id);
		return $this->getData('festival/get', $args);
	}
}

//$eventsCalendar = new eventsCalendarClient($url, null, 0);

//print_r ( $eventsCalendar->upcomingEvents() );
//print_r ( $eventsCalendar->filteredEventsList(array('arranger_id' => 1)) );
