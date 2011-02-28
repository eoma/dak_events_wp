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
		if(!is_array($options))
			$options = array();
		
		if (!isset($options['eventServerUrl']))
			$options['eventServerUrl'] = '';

		if (!isset($options['dateFormat']))
			$options['dateFormat'] = 'Y-m-d';
		if (!isset($options['timeFormat']))
			$options['timeFormat'] = 'H:i';
		
		if (!isset($options['cache']))
			$options['cache'] = eventsCalendarClient::CACHE_WP;

		if (!isset($options['cacheTime']))
			$options['cacheTime'] = 600;
		
		if (!isset($options['eventPageId'])) 
			$options['eventPageId'] = null; // Page id (integer)

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

			if (isset($_POST['eventPageId'])) 
				$options['eventPageId'] = intval($_POST['eventPageId']);

			if (isset($_POST['cacheTime'])) {
				if (intval($_POST['cacheTime']) >= 1) {
					// Convert to seconds
					$options['cacheTime'] = intval(round($_POST['cacheTime'])) * 60;
				}
			}

			update_option('optionsDakEventsWp', $options);
		}

		if (isset($_GET['clearCache']) && ($_GET['clearCache'] == 1)) {
			$eventsCalendarClient = new eventsCalendarClient($options['eventServerUrl'], null, $options['cache'], $options['cacheTime']);
			$eventsCalendarClient->clearCache();
			$eventsCalendarClient = null;
                }
?>
    <div class="wrap">
      <h2><?php _e('Events Calendar Options', 'dak_events_wp') ?></h2>
    </div>
    <?php if ($options['cache'] > 0): ?>
    <p>
      <a href="?page=dak-events-calendar&amp;clearCache=1" class="button"><?php _e('Clear cache', 'dak_event_wp') ?></a>
    </p>
    <?php endif ?>
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
        <tr>
          <th><label for="dew_cacheTime"><?php _e('Default cache time (minutes)', 'dak_Events_wp') ?></label></th>
          <td>
            <input type="text" name="cacheTime" id="dew_cacheTime" value="<?php echo intval(round($options['cacheTime'] / 60)) ?>" />
          </td>
        </tr>
        <tr>
          <th><label for="dew_eventPageId"><?php _e('At which page shall events in lists link to?', 'dak_events_wp') ?></label></th>
          <td>
            <select name="eventPageId" id="dew_eventPageId">
              <option value="" <?php if ($options['eventPageId'] == 0) echo 'seleted="selected"' ?>>
                -- <?php _e("Don't link to any internal page", 'dak_events_wp') ?> --
              </option>
              <?php foreach (get_pages() as $p) {
                $selected = '';
                
                if ($options['eventPageId'] == $p->ID) {
                  $selected = 'selected="selected"';
				}
                
				echo "<option value='{$p->ID}' {$selected}>";
				echo $p->post_title;
				echo "</option>\n";
              } ?>
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
