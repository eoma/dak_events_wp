<?php

class DEW_format {

	static public function eventInList() {
		// All named arguments are required in the format
		$format = <<<EOT
<div class="dew_showEvent">
  <span class="event_name">%(title)s</span>
  <span class="location_name">%(category)s i %(location)s <span class="event_time">%(renderedTime)s</span></span>
</div>
<div class="dew_eventElem dew_hide">
  <div class="dew_content">
    %(leadParagraph)s
  </div>
  <div class="dew_data">
    <strong>Når:</strong> %(renderedDate)s<br />
    <strong>Hvor:</strong> %(location)s<br />
    <strong>Arrangør:</strong> %(arranger)s<br />
    <strong>Type:</strong> %(category)s<br />
  </div>
</div>
EOT;

		return $format;
	}

	static public function fullEvent() {
		// All named arguments are required in the format
		$format = <<<EOT
<h3>%(title)s</h3>
<p class="agenda_data">
  %(category)s i %(location)s<br />
  Arrangør: %(arranger)s
</p>
%(leadParagraph)s
<span class="agenda_read_more">
  Les mer
</span>
<div class="agenda_description">
  %(description)s
</div>
EOT;

		return $format;
	}

	static public function eventDetailBox() {
		// All named arguments are required in the format
		$format = <<<EOT
<p>
  <strong>Tittel:</strong> %(title)s<br />
  <strong>Når:</strong> %(renderedDate)s<br />
  <strong>Hvor:</strong> %(location)s<br />
  <strong>Arrangør:</strong> %(arranger)s<br />
  <strong>Type:</strong> %(category)s<br />
</p>
EOT;

		return $format;
	}

}
