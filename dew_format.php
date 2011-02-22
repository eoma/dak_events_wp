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
<h3>%(title)s</h3>
<p class=\"agenda_data\">
  " . sprintf(__('%s in %s', 'dak_events_wp') , '%(category)s', '%(location)s') . "<br />
  " . __('Starts:', 'dak_events_wp') . " %(startTime)s<br />
  " . __('Arranger:', 'dak_events_wp') . " %(arranger)s
</p>
%(leadParagraph)s
<span class=\"agenda_read_more\">
  " . __('Read more', 'dak_events_wp') . "
</span>
<div class=\"agenda_description\">
  %(description)s
</div>
</div>";

		return $format;
	}

	static public function fullEvent() {
		// All named arguments are required in the format
		$format = "
<div class=\"agenda_event_wrapper\">
  <h2>%(title)s</h2>
  <p class=\"agenda_data\">
    " . sprintf(__('%s in %s', 'dak_events_wp') , '%(category)s', '%(location)s') . "<br />
    " . __('Starts:', 'dak_events_wp') . " %(startTime)s<br />
    " . __('Arranger:', 'dak_events_wp') . " %(arranger)s
  </p>
  <p>%(leadParagraph)s</p>
  %(description)s
  <p><small><a href='%(urlOriginal)s'>" . __('Orginal event', 'dak_events_wp') . "</a></small></p>
</div>";

		return $format;
	}

	static public function eventDetailBox() {
		// All named arguments are required in the format
		$format = "
<p>
  <strong>" . __('Title:', 'dak_events_wp') . "</strong> %(title)s<br />
  <strong>" . __('When:', 'dak_events_wp') . "</strong> %(renderedDate)s<br />
  <strong>" . __('Where:', 'dak_events_wp') . "</strong> %(location)s<br />
  <strong>" . __('Arranger:', 'dak_events_wp') . "</strong> %(arranger)s<br />
  <strong>" . __('Type:', 'dak_events_wp') . "</strong> %(category)s<br />
</p>";

		return $format;
	}
}
