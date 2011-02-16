<?php

if ( isset($_GET['eventStylesheet']) && ($_GET['eventStylesheet'] == 1) ) {
	$eventCss = dirname($_SERVER['SCRIPT_NAME']) . '/css/eventjs.css';
	header('Content-Type: text/javascript');
	echo <<<EOT
var eventStylesheet = document.createElement('link');
eventStylesheet.rel = 'stylesheet';
eventStylesheet.type = 'text/css';
eventStylesheet.href = '{$eventCss}';
document.getElementsByTagName('head')[0].appendChild(eventStylesheet);
EOT;
}
