<?php

/**
 * This abstract class DEW_arrangement will function as
 * a kind of shell class enabling you to use an event or festival
 * in an "Object Oriented" way.
 *
 * Please use either DEW_event for events or DEW_festival for festivals.
 *
 * It will not give you direct access to the arrangement object.
 */
abstract class DEW_arrangement {
	protected $arr;

	protected $startTimestamp;
	protected $endTimestamp;
	protected $location;

	public function __construct(stdClass $rawArrangement) {
		$this->arr = $rawArrangement;

		$this->startTimestamp = DEW_tools::dateStringToTime($this->arr->startDate, $this->arr->startTime);
		$this->endTimestamp = DEW_tools::dateStringToTime($this->arr->endDate, $this->arr->endTime);			
		$this->location = DEW_tools::getLocationFromEvent($this->arr);
	}

	public function getStartTimestamp() { return $this->startTimestamp; }
	public function getEndTimestamp() { return $this->endTimestamp; }

	/** Child classes must implement this function */
	abstract public function getUrl ($getInternalUrl = true);

	/**
	 * Return the url to an ical feed
     */
	public function getICalUrl() {
		return $this->arr->ical;
	}

	/**
	 * Returns a string representation of the location
	 * where the arrangement will be happening.
	 */
	public function getLocation() {
		return $this->location;
	}

	/** Return the title of the arrangement */
	public function getTitle() { return $this->arr->title; }

	/** Return the lead paragraph */
	public function getLeadParagraph() { return $this->arr->leadParagraph; }

	/** Return the arrangement's description if it is not empty.
	 * If it is empty it will return an empty string
	 */
	public function getDescription() {
		if (!empty($this->arr->description)) {
			return $this->arr->description;
		}

		return '';
	}

	/** Computes whether or not the event ends on the same day or not */
	public function endSameDay() {
		return DEW_tools::eventEndSameDay($this->arr);
	}

	/** Determines whether or not cover charge has been defined */
	public function hasCoverCharge () {
		return !empty($this->arr->covercharge);
	}

	/** Returns the cover charge */
	public function getCoverCharge () {
		return $this->arr->covercharge;
	}

	/**
	 * Gives you a string representation of the arrangement's duration.
	 * You can specify the date format and/or the time format.
	 * It will use one of two string formats based on whether the event ends on the same day.
	 */
	public function getFormattedDuration($dateFormat = null, $timeFormat = null) {
		if (is_null($dateFormat) || is_null($timeFormat)) {
			$options = DEW_Management::getOptions();
			if (is_null($dateFormat))
				$dateFormat = $options['dateFormat'];

			if (is_null($timeFormat))
				$timeFormat = $options['timeFormat'];
		}

		if ($this->endSameDay()) {
			return sprintf(__('%s from %s to %s', 'dak_events_wp'),
				date_i18n($dateFormat, $this->getStartTimestamp()),
				date_i18n($timeFormat, $this->getStartTimestamp()),
				date_i18n($timeFormat, $this->getEndTimestamp())
			);
		} else {
			return sprintf(__('%s from %s to %s %s', 'dak_events_wp'),
				date_i18n($dateFormat, $this->getStartTimestamp()),
				date_i18n($timeFormat, $this->getStartTimestamp()),
				date_i18n($dateFormat, $this->getEndTimestamp()),
				date_i18n($timeFormat, $this->getEndTimestamp())
			);
		}
	}

	/**
	 * Will determine whether the event happens now
	 *
	 * @return boolean
	 */
	public function happensNow() {
		// Try to compute current timestamp in active timezone
		// Ugly hack, not to be trusted in the long term
		$now = time() + get_option('gmt_offset') * 3600;

		return ( ($this->getStartTimestamp() < $now) && ($this->getEndTimestamp() > $now) );
	}

	public function getUpdatedAt () { return $this->arr->updated_at; }
	public function getCreatedAt () { return $this->arr->created_at; }
}

class DEW_event extends DEW_arrangement {

	/**
	 * Returns url to the event
	 *
	 * @param bool $getInternalUrl If the url to the internal page is to be returned, else external will be returned
	 * @return string
	 */
	public function getUrl($getInternalUrl = true) {
		if ($getInternalUrl) {
			return DEW_tools::generateLinkToArrangement($this->arr, 'event');
		} else {
			return $this->arr->url;
		}
	}

	/**
	 * Returns name of the arranger
	 *
	 * @return string
	 */
	public function getArranger() {
		return $this->arr->arranger->name;
	}

	/**
	 * Returns a concatenated string of all categories an
	 * event is associated with.
	 *
	 * @return string
	 */
	public function getCategory() {
		$tmp = array();

		foreach ($this->arr->categories as $c) {
			$tmp[] = $c->name;
		}

		$categories = implode(', ', $tmp);

		return $categories;
	}

	/** Determines whether or not the event is associated with a festival */
	public function hasFestival() {
		return ($this->arr->festival_id > 0);
	}

	/** Returns the festival title */
	public function getFestivalTitle() {
		if ($this->hasFestival()) {
			return $this->arr->festival->title;
		}
	}

	/**
	 * Returns url to the associated festival
	 *
	 * @param bool $getInternalUrl If the url to the internal page is to be returned, else external will be returned
	 * @return string
	 */
	public function getFestivalUrl($getInternalUrl = true) {
		if ($this->hasFestival()) {
			if ($getInternalUrl) {
				return DEW_tools::generateLinkToArrangement($this->arr->festival, 'festival');
			} else {
				return $this->arr->festival->url;
			}
		}
	}

	/** Returns associated festival's start timestamp */
	public function getFestivalStartTimestamp() {
		return DEW_tools::dateStringToTime($this->arr->festival->startDate, $this->arr->festival->startTime);
	}

	/** Returns associated festival's end timestamp */
	public function getFestivalEndTimestamp() {
		return DEW_tools::dateToString($this->arr->festival->endDate, $this->arr->festival->endTime);
	}

	/** Determines whether or not the event contains a primary picture */
	public function hasPrimaryPicture() {
		return !is_null($this->arr->primaryPicture);
	}

	/**
	 * Will return event object's primary picture object
	 * if it is defined. The picture object should be used
	 * together with the the DEW_tools::getPicture() method
	 */
	public function getPrimaryPicture () {
		if ($this->hasPrimaryPicture()) {
			return $this->arr->primaryPicture;
		}
	}
}

class DEW_festival extends DEW_arrangement {
	/**
	 * Returns url to the event
	 *
	 * @param bool $getInternalUrl If the url to the internal page is to be returned, else external will be returned
	 * @return string
	 */
	public function getUrl($getInternalUrl = true) {
		if ($getInternalUrl) {
			return DEW_tools::generateLinkToArrangement($this->arr, 'festival');
		} else {
			$this->arr->url;
		}
	}

	/**
	 * Returns a concatenated string of all arrangers a
	 * festival is associated with.
	 *
	 * @return string
	 */
	public function getArranger() {
		$tmp = array();

		foreach ($this->arr->arrangers as $a) {
			$tmp[] = $a->name;
		}

		$arrangers = implode(', ', $tmp);

		return $arrangers;
	}
}
