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
			$options['cache'] = eventsCalendarClient::CACHE_WP;
		}
		$options['cache'] = intval($options['cache']);
		if (isset($_POST['optionsDakEventsWpSubmitted']) && $_POST['optionsDakEventsWpSubmitted']) {
			//echo var_dump($_POST);
			$options['eventServerUrl'] = !empty($_POST['eventServerUrl']) ? trim($_POST['eventServerUrl']) : '';

			if ( ! empty($_POST['eventServerUrl']) ) {
				$url = trim(strval($_POST['eventServerUrl']));

				// We'll only accept root application url.
				if (substr($url, -8) == 'api/json') {
					$url = substr($url, 0, -8);
				} else if (substr($url, -9) == 'api/json/') {
					$url = substr($url, 0, -9);
				}

				if (substr($url, -1) != '/') {
					$_POST['eventServerUrl'] = $url . '/';
				} else {
					$_POST['eventServerUrl'] = $url;
				}
			} else {
				$options['eventServerUrl'] = '';
			}

			$options['dateFormat'] = !empty($_POST['dateFormat']) ? trim($_POST['dateFormat']) : 'Y-m-d';
			$options['timeFormat'] = !empty($_POST['timeFormat']) ? trim($_POST['timeFormat']) : 'H:i';

			if (isset($_POST['cache']) && in_array(intval($_POST['cache']), array(0,1,2))) {
				$options['cache'] = intval($_POST['cache']);
			} else {
				$options['cache'] = eventsCalendarClient::CACHE_WP;
			}

			update_option('optionsDakEventsWp', $options);
		}
?>
    <div class="wrap">
      <h2><?php _e('Events Calendar Options', 'dak_events_wp') ?></h2>
    </div>
    <form name="optionsDakEventsWp" method="post" action="?page=dak-events-calendar">
      <p class="submit">
        <input type="submit" name="submit" value="Update Options &raquo;">
      </p>
      <table>
        <tr>
          <th><label for="dew_eventServerUrl"><?php _e('URL to event server', 'dak_events_wp') ?></label></th>
          <td><input type="text" id="dew_eventServerUrl" name="eventServerUrl" size="64" value="<?php echo $options['eventServerUrl'] ?>" /></td>
        </tr>
        <tr>
          <th><label for="dew_dateFormat"><?php _e('Date format', 'dew') ?></label></th>
          <td><input type="text" id="dew_dateFormat" name="dateFormat" size="10" value="<?php echo $options['dateFormat'] ?>" /> <small><?php printf(__('(see %s)', 'dak_events_wp'), '<a href="http://php.net/date">php date()</a>') ?></small></td>
        </tr>
        <tr>
          <th><label for="dew_timeFormat"><?php _e('Time format', 'dak_events_wp') ?></label></th>
          <td><input type="text" id="dew_timeFormat" name="timeFormat" size="10" value="<?php echo $options['timeFormat'] ?>" /> <small><?php printf(__('(see %s)', 'dak_events_wp'), '<a href="http://php.net/date">php date()</a>') ?></small></td>
        </tr>
        <tr>
          <th><label for="dew_cache"><?php _e('Use cache?', 'dak_events_wp') ?></label></th>
          <td>
	    <select name="cache" id="dew_cache">
	      <option value="0" <?php if ($options['cache'] == 0) echo 'selected="selected"' ?>> <?php _e('None', 'dak_events_wp') ?></option>
	      <option value="1" <?php if ($options['cache'] == 1) echo 'selected="selected"' ?>> <?php _e('APC (directly)', 'dak_events_wp') ?></option>
	      <option value="2" <?php if ($options['cache'] == 2) echo 'selected="selected"'?>> <?php _e('WordPress own cache', 'dak_events_wp') ?></option>
	    </select>
	  </td>
        </tr>
      </table>
      <input type="hidden" name="optionsDakEventsWpSubmitted" value="1" />
      <p class="submit">
        <input type="submit" name="submit" value="<?php _e('Update Options', 'dak_events_wp') ?> &raquo;">
      </p>
    </form>
<?php
  }
}
endif;
