<?php
class DEW_format {

	static public function eventInList() {
		// All named arguments are required in the format
		$format = "
<div class=\"dew_showEvent\">
  <span class=\"event_name\">%(title)s</span>
  <span class=\"location_name\">%(category)s - %(location)s <span class=\"event_time\">%(renderedTime)s</span></span>
</div>
<div class=\"dew_eventElem dew_hide\">
  <div class=\"dew_content\">
    %(leadParagraph)s
  </div>
  <div class=\"dew_data\">
    <strong>" . __('When:', 'dak_events_wp') . "</strong> %(renderedDate)s<br />
    <strong>" . __('Where:', 'dak_events_wp') . "</strong> %(location)s<br />
    <strong>" . __('Arranger:', 'dak_events_wp') . "</strong> %(arranger)s<br />
    <strong>" . __('Type:', 'dak_events_wp') . "</strong> %(category)s<br />
    %(extra)s
  </div>
</div>";

		return $format;
	}

	static public function agendaFullEvent() {
		// All named arguments are required in the format
		$format = "
<div class=\"agenda_event_wrapper\">
  <h3><a href=\"%(readMore)s\">%(title)s</a></h3>
  <p class=\"agenda_data\">
    " . sprintf(__('%s in %s', 'dak_events_wp') , '%(category)s', '%(location)s') . "<br />
    " . __('Starts:', 'dak_events_wp') . " %(startTime)s<br />
    " . __('Arranger:', 'dak_events_wp') . " %(arranger)s<br />
    %(extra)s
  </p>
  <p>%(leadParagraph)s</p>
</div>";

		return $format;
	}
	
	static public function agendaCompactList() {
		$format = "
<div class='agenda_compact_event_wrapper'>
%(startTime)s - <a href=\"%(readMore)s\">%(title)s</a> <br />
%(category)s - %(location)s
</div>
";

		return $format;
	}
	
	static public function agendaEventCollection() {
		$format = "
<h2 class='agenda_collection_name %(extraCollectionClass)s'>%(monthName)s</h2>
<div class='agenda_event_collection %(extraClass)s' id='%(id)s'>
  %(eventCollection)s
</div>
";

		return $format;
	}

	static public function agendaEventDateCollection () {
		$format = "
<h2 class='agenda_box'>
  <span class='agenda_day_name'>%(dayName)s</span>
  <span class='agenda_day_number'>%(dayNumber)s</span>
  <span class='agenda_month_name'>%(monthName)s</span>
</h2>
<div class='event_date_list'>
  %(eventCollection)s
</div>
";

		return $format;
	}

	static public function fullEvent(array $config = array()) {
		// All named arguments are required in the format

		/**
		 * $config can be an associative array
		 * array(
		 * 	'no_title' => bool, // Whether to include the title or not
		 * )
		 */


		$title = '<h2>%(title)s</h2>';
		if (isset($config['no_title']) && ($config['no_title'] == true)) {
			$title = '';
		}

		$format = "
<div class=\"agenda_event_wrapper\">
  " . $title . "
  <p class=\"agenda_data\">
    " . sprintf(__('%s in %s', 'dak_events_wp') , '%(category)s', '%(location)s') . "<br />
    " . __('Starts:', 'dak_events_wp') . " %(renderedDate)s<br />
    " . __('Arranger:', 'dak_events_wp') . " %(arranger)s<br />
    %(extra)s
    <a href=\"%(iCalUrl)s\">" . __('Add event to your calendar', 'dak_events_wp') . "</a>
    <a href=\"%(googleCalUrl)s\" target=\"_blank\">" . __('Google calendar', 'dak_events_wp') . "</a>
  </p>
  <p>%(leadParagraph)s</p>
  %(description)s
  <p><small><a href='%(urlOriginal)s'>" . __('Orginal event', 'dak_events_wp') . "</a></small></p>
</div>";

		return $format;
	}

	static public function fullFestival(array $config = array()) {
		// All named arguments are required in the format

		/**
		 * $config can be an associative array
		 * array(
		 * 	'no_title' => bool, // Whether to include the title or not
		 * )
		 */


		$title = '<h2>%(title)s</h2>';
		if (isset($config['no_title']) && ($config['no_title'] == true)) {
			$title = '';
		}

		$format = "
<div class=\"agenda_event_wrapper\">
  " . $title . "
  <p class=\"agenda_data\">
    '%(location)s' <br />
    " . __('Starts:', 'dak_events_wp') . " %(renderedDate)s<br />
    " . __('Arranger:', 'dak_events_wp') . " %(arranger)s<br />
    %(extra)s
    <a href=\"%(iCalUrl)s\">" . __('Add festival to your calendar', 'dak_events_wp') . "</a>
    <a href=\"%(googleCalUrl)s\" target=\"_blank\">" . __('Google calendar', 'dak_events_wp') . "</a>
    <br />
    <a href=\"#dew_festivalEvents\">" . __('Jump to the events', 'dak_events_wp') . "</a>
  </p>
  <p>%(leadParagraph)s</p>
  %(description)s

  <div id='dew_festivalEvents'>
   %(festivalEvents)s
  </div>

  <p><small><a href='%(urlOriginal)s'>" . __('Orginal festival', 'dak_events_wp') . "</a></small></p>


</div>";

		return $format;
	}

	static public function eventDetailBox() {
		// All named arguments are required in the format
		$format = "
<div class='dew_eventDetailBox'>
  <div class='dew_eventDate'>
    <span class='agenda_day_name'>%(startDayName)s</span>
    <span class='agenda_day_number'>%(dayInMonth)s</span>
    <span class='agenda_month_name'>%(monthName)s</span>
  </div>
  <div class='dew_eventDetails'>
    <span class='dew_eventTitle'>%(title)s</span><br />
    " . sprintf(__('%s in %s', 'dak_events_wp'), '%(category)s', '%(location)s') . "<br />
    " . __('Starts', 'dak_events_wp') . " %(startTime)s<br />
    " . __('Arranged by', 'dak_events_wp') . " %(arranger)s<br />
    %(extra)s
    <a href=\"%(iCalUrl)s\">" . __('Add event to your calendar', 'dak_events_wp') . "</a>
    <a href=\"%(googleCalUrl)s\" target=\"_blank\">" . __('Google calendar', 'dak_events_wp') . "</a>
  </div>
</div>";

		return $format;
	}

	static public function festivalDetailBox() {
		// All named arguments are required in the format
		$format = "
<div class='dew_eventDetailBox'>
  <div class='dew_eventDate'>
    <span class='agenda_day_name'>%(startDayName)s</span>
    <span class='agenda_day_number'>%(dayInMonth)s</span>
    <span class='agenda_month_name'>%(monthName)s</span>
  </div>
  <div class='dew_eventDetails'>
    <span class='dew_eventTitle'>%(title)s</span><br />
    %(location)s<br />
    " . __('Starts', 'dak_events_wp') . " %(startTime)s<br />
    " . __('Ends', 'dak_events_wp') . " %(endDatetime)s<br />
    " . __('Arranged by', 'dak_events_wp') . " %(arranger)s<br />
    %(extra)s
    <a href=\"%(iCalUrl)s\">" . __('Add festival to your calendar', 'dak_events_wp') . "</a>
    <a href=\"%(googleCalUrl)s\" target=\"_blank\">" . __('Google calendar', 'dak_events_wp') . "</a>
  </div>
</div>";

		return $format;
	}
}
