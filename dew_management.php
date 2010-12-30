<?php

if(!class_exists('DEW_Management')):
require_once(DEW_PREFIX . '/eventsCalendarClient.php');

/**
 * Dashboard management.
 *
 * Enables users to set event server url and certain display options.
 */
class DEW_Management {


	/**
	 * Provides the admin option panel.
	 */
	function options() {
		$options = get_option('optionsDakEventsWp');
		if(!is_array($options)) {
			$options = array();
			$options['eventServerUrl'] = '';
			$options['dateFormat'] = 'Y-m-d';
			$options['timeFormat'] = 'H:i';
			$options['cache'] = 0;
		}
		if (isset($_POST['optionsDakEventsWpSubmitted']) && $_POST['optionsDakEventsWpSubmitted']) {
			//echo var_dump($_POST);
			$options['eventServerUrl'] = !empty($_POST['eventServerUrl']) ? trim($_POST['eventServerUrl']) : '';

			if ( ! empty($_POST['eventServerUrl']) ) {
				$url = trim(strval($_POST['eventServerUrl']));

				// We'll only accept root application url.
				if (substr($usrl, -8) == 'api/json') {
					$_POST['eventServerUrl'] = substr($url, 0, -8);
				} else if (substr($url, -9) == 'api/json/') {
					$_POST['eventServerUrl'] = substr($url, 0, -9);
				} else {
					$_POST['eventServerUrl'] = $url;
				}
			} else {
				$options['eventServerUrl'] = '';
			}

			$options['dateFormat'] = !empty($_POST['dateFormat']) ? trim($_POST['dateFormat']) : 'Y-m-d';
			$options['timeFormat'] = !empty($_POST['timeFormat']) ? trim($_POST['timeFormat']) : 'H:i';

			if (!empty($_POST['cache']) && ($_POST['cache'] == 'on')) {
				$options['cache'] = 1;
			} else {
				$options['cache'] = 0;
			}

			update_option('optionsDakEventsWp', $options);
		}
?>
    <div class="wrap">
      <h2>Events Calendar Options</h2>
    </div>
    <form name="optionsDakEventsWp" method="post" action="?page=dak-events-calendar">
      <p class="submit">
        <input type="submit" name="submit" value="Update Options &raquo;">
      </p>
      <table>
        <tr>
          <th><label for="dew_eventServerUrl">URL to event server</label></th>
          <td><input type="text" id="dew_eventServerUrl" name="eventServerUrl" size="64" value="<?php echo $options['eventServerUrl'] ?>" /></td>
        </tr>
        <tr>
          <th><label for="dew_dateFormat">Date format</label></th>
          <td><input type="text" id="dew_dateFormat" name="dateFormat" size="10" value="<?php echo $options['dateFormat'] ?>" /> <small>(see <a href="http://php.net/date">php date()</a></small></td>
        </tr>
        <tr>
          <th><label for="dew_timeFormat">Time format</label></th>
          <td><input type="text" id="dew_timeFormat" name="timeFormat" size="10" value="<?php echo $options['timeFormat'] ?>" /> <small>(see <a href="http://php.net/date">php date()</a></small></td>
        </tr>
        <tr>
          <th><label for="dew_cache">Use cache?</label></th>
          <td><input id="dew_cache" type="checkbox" name="cache" <?php if ($options['cache'] == 1) echo 'checked="checked"' ?> /></td>
        </tr>
      </table>
      <input type="hidden" name="optionsDakEventsWpSubmitted" value="1" />
      <p class="submit">
        <input type="submit" name="submit" value="Update Options &raquo;">
      </p>
    </form>
<?php
  }
}
endif;
