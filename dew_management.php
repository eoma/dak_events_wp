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
		}
		if (isset($_POST['optionsDakEventsWpSubmitted']) && $_POST['optionsDakEventsWpSubmitted']) {
			//echo var_dump($_POST);
			$options['eventServerUrl'] = !empty($_POST['eventServerUrl']) ? trim($_POST['eventServerUrl']) : '';
			$options['dateFormat'] = !empty($_POST['dateFormat']) ? trim($_POST['dateFormat']) : 'Y-m-d';
			$options['timeFormat'] = !empty($_POST['timeFormat']) ? trim($_POST['timeFormat']) : 'H:i';

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
          <th>URL to event server:</th>
          <td><input type="text" name="eventServerUrl" size="64" value="<?php echo $options['eventServerUrl'] ?>" /></td>
        </tr>
        <tr>
          <th>Date format</th>
          <td><input type="text" name="dateFormat" size="10" value="<?php echo $options['dateFormat'] ?>" /> <small>(see <a href="http://php.net/date">php date()</a></small></td>
        </tr>
        <tr>
          <th>Time format</th>
          <td><input type="text" name="timeFormat" size="10" value="<?php echo $options['timeFormat'] ?>" /> <small>(see <a href="http://php.net/date">php date()</a></small></td>
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
