<?php

class DEW_format {

	static public function eventInList() {
		// All named arguments are required in the format
		$format = <<<EOT
<div class="dew_showEvent"><span class="event_name">%(title)s</span>  <span class="location_name">%(location)s</span></div>
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
%(leadParagraph)s
%(description)s
<p>
  <strong>Når:</strong> %(renderedDate)s<br />
  <strong>Hvor:</strong> %(location)s<br />
  <strong>Arrangør:</strong> %(arranger)s<br />
  <strong>Type:</strong> %(category)s<br />
</p>
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
