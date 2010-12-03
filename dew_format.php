<?php

class DEW_format {

	static public function eventInList() {
		// All named arguments are required in the format
		$format = <<<EOT
<div class="dew_showEvent">%(title)s</div>
<div class="dew_eventElem dew_hide">
  <div class="dew_content">
    %(leadParagraph)s
  </div>
  <div class="dew_data">
    <strong>When?</strong> %(renderedDate)s<br />
    <strong>Where?</strong> %(location)s<br />
    <strong>Arranger?</strong> %(arranger)s<br />
    <strong>Category?</strong> %(category)s<br />
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
  <strong>When?</strong> %(renderedDate)s<br />
  <strong>Where?</strong> %(location)s<br />
  <strong>Arranger?</strong> %(arranger)s<br />
  <string>Category?</strong> %(category)s
</p>
EOT;

		return $format;
	}

	static public function eventDetailBox() {
		// All named arguments are required in the format
		$format = <<<EOT
<p>
  <strong>What?</strong> %(title)s<br />
  <strong>When?</strong> %(date)s<br />
  <strong>Location?</strong> %(location)s<br />
  <strong>Arranger?</strong> %(arranger)s<br />
  <strong>Category?</strong> %(category)s
</p>
EOT;

		return $format;
	}

}
