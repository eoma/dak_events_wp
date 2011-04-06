<?php
if(!class_exists('DEW_Widget')) :
require_once(DEW_PREFIX . '/eventsCalendarClient.php');

/**
 * Displays the sidebar widget.
 *
 * This can either be the small calendar or the event list, 
 * depending on the widget control option.
 */
class DEW_Widget extends WP_Widget {
  
	public $eventServerURL;
	private $options;

	function __construct() {
		$this->options = get_option('optionsDakEventsWp');
		$this->eventServerUrl = $this->options['eventServerUrl'];
		parent::__construct(false, 'DAK Events');
	}

	function makeSelectOption ($arrObj, $valueKey, $descKey, $selectedValue = null) {
		$select = "";

		foreach ($arrObj as $o) {
			if (isset($selectedValue) && ($o->$valueKey == $selectedValue)) {
				$select .= "<option value='" . $o->$valueKey . "' selected='selected'>" . $o->$descKey . "</option>\n";
			} else {
				$select .= "<option value='" . $o->$valueKey . "'>" . $o->$descKey . "</option>\n";
			}
		}

		return $select;
	}

	/**
	 * Will print a form to setup an event calendar widget
	 * 
	 * @param array $instance contains settings for the calendar widget, like title or filter
	 */
	function form($instance) {
		if (empty($instance)) $instance = array();
		if (empty($instance['title'])) $instance['title'] = 'DAK Events Calendar';
		if (empty($instance['type'])) $instance['type'] = 'list';
		if (empty($instance['listCount'])) $instance['listCount'] = 10;
		// $instance['daysInFuture'] describes how many days into the future it shall display, limited by listCount.
		// If 0, infite days, until maximum number of events defined in listCount is 
		if (empty($instance['filter'])) $instance['filter'] = array();
		if (empty($instance['filter']['daysInFuture']))  $instance['filter']['daysInFuture'] = 0;
		if (empty($instance['filter']['arranger_id'])) $instance['filter']['arranger_id'] = array();
		if (empty($instance['filter']['location_id'])) $instance['filter']['location_id'] = array();
		if (empty($instance['filter']['category_id'])) $instance['filter']['category_id'] = array();

		// No cache for event calendar client when setting up widgets
		$client = new eventsCalendarClient($this->eventServerUrl, null, eventsCalendarClient::CACHE_NONE);

		$arrangerList = $client->arrangerList()->data;
		$locationList = $client->locationList()->data;
		$categoryList = $client->categoryList()->data;

		$title = esc_attr($instance['title']);
		$titleName = $this->get_field_name('title');
		$title_id = $this->get_field_id('title');
		
		$typeName = $this->get_field_name('type');
		$type_id = $this->get_field_id('type');

		$listCountName = $this->get_field_name('listCount');
		$listCount_id = $this->get_field_id('listCount');

		$daysInFutureName = $this->get_field_name('filter][daysInFuture');
		$daysInFuture_id = $this->get_field_id('daysInFuture');
		
		$baseName = $this->get_field_name('');
		$base_id = $this->get_field_id('base');

		$arranger_idName = $this->get_field_name('filter][arranger_id][');
		$arrangerList_id = $this->get_field_id('arrangerList');
		$chosenArrangerList_id = $this->get_field_id('chosenArrangerList');
		$addArrangerButton_id = $this->get_field_id('addArrangerButton');

		$location_idName = $this->get_field_name('filter][location_id][');
		$locationList_id = $this->get_field_id('locationList');
		$chosenLocationList_id = $this->get_field_id('chosenLocationList');
		$addLocationButton_id = $this->get_field_id('addLocationButton');

		$category_idName = $this->get_field_name('filter][category_id][');
		$categoryList_id = $this->get_field_id('categoryList');
		$chosenCategoryList_id = $this->get_field_id('chosenCategoryList');
		$addCategoryButton_id = $this->get_field_id('addCategoryButton');

		$eventListOptions_id = $this->get_field_id('eventListOptions');

		?>
 <input type="hidden" id="<?php echo $base_id ?>" value="<?php echo $baseName ?>" />
 <p>
  <label for="<?php echo $title_id; ?>"><?php _e('Title:', 'dak_events_wp') ?>
   <input type='text' id='<?php echo $title_id; ?>' name='<?php echo $titleName; ?>' value='<?php echo $title; ?>'/>
  </label>
 </p>
 <p style="text-align:center;">
  <label for="<?php echo $type_id; ?>">
   <?php _e('Calendar Type:', 'dak_events_wp') ?>
   <select class="dew_type" name="<?php echo $typeName; ?>" id="<?php echo $type_id; ?>">
    <option value="calendar"><?php _e('Calendar', 'dak_events_wp') ?></option>
    <option value="list" <?php if ( isset($instance['type']) && 'list' == $instance['type'] ) echo 'selected="selected"'; ?>><?php _e('Event List', 'dak_events_wp') ?></option>
   </select>
  </label>
 </p>
 <div id="<?php echo $eventListOptions_id; ?>" style="<?php if ( 'list' != $instance['type'] ) echo 'display: none;'; ?>">
  <p>
   <span style="font-weight: bold"><?php _e('Event List options', 'dak_events_wp') ?></span>
  </p>
  <p>
   <label for="<?php echo $listCount_id; ?>">
    <?php _e('Number of events:', 'dak_events_wp') ?>
    <input style="width: 30px;" type="text" id="<?php echo $listCount_id; ?>" name="<?php echo $listCountName; ?>" value="<?php echo $instance['listCount']; ?>" />
   </label>
  </p>
  <p>
   <label for="<?php echo $daysInFuture_id; ?>">
    <?php _e('How many days into the future shall it display (if 0, infinite days, only limited by number of events):', 'dak_events_wp') ?>
    <input style="width: 30px;" type="text" id="<?php echo $daysInfuture_id; ?>" name="<?php echo $daysInFutureName; ?>" value="<?php echo $instance['filter']['daysInFuture']; ?>" />
   </label>
  </p>
 </div>
 <span><?php _e('Requires events to be', 'dak_events_wp') ?></span>
<!-- Arrangers -->
 <div style="margin-top: 0.2em;">
  <span style="font-weight: bold"><?php _e('arranged by', 'dak_events_wp') ?></span>
  <div id="<?php echo $chosenArrangerList_id; ?>">
  <?php
  		if (count($instance['filter']['arranger_id']) > 0) {
			foreach ($arrangerList as $a) {
				if (in_array($a->id, $instance['filter']['arranger_id'])) {
					echo '<div id="' . $chosenArrangerList_id . '-' . $a->id . '">';
					echo '<input type="hidden" name="' . $arranger_idName . '" value="' . $a->id . '"/>';
					echo '<span>' . $a->name . ' </span>';
					echo '<button class="dew_deleteElement" type="button"><small>' . __('Delete', 'dak_events_wp') . '</small></button>';
					echo '</div>';
				}
			}
		}
  ?>
  </div>
  <select id="<?php echo $arrangerList_id; ?>">
   <?php echo $this->makeSelectOption($arrangerList, 'id', 'name'); ?>
  </select>
  <button class="dew_addArrangerButton" id="<?php echo $addArrangerButton_id; ?>" type="button"><?php _e('Add arranger', 'dak_events_wp') ?></button>
 </div>
<!-- Locations -->
 <div style="margin-top: 0.2em;">
  <span style="font-weight: bold"><?php _e('located at', 'dak_events_wp') ?></span>
  <div id="<?php echo $chosenLocationList_id; ?>">
  <?php
  		if (count($instance['filter']['location_id']) > 0) {
			foreach ($locationList as $a) {
				if (in_array($a->id, $instance['filter']['location_id'])) {
					echo '<div id="' . $chosenLocationList_id . '-' . $a->id . '">';
					echo '<input type="hidden" name="' . $location_idName . '" value="' . $a->id . '"/>';
					echo '<span>' . $a->name . ' </span>';
					echo '<button class="dew_deleteElement" type="button"><small>' . __('Delete', 'dak_events_wp') . '</small></button>';
					echo '</div>';
				}
			}
		}
  ?>
  </div>
  <select id="<?php echo $locationList_id; ?>">
   <?php echo $this->makeSelectOption($locationList, 'id', 'name'); ?>
  </select>
  <button class="dew_addLocationButton" id="<?php echo $addLocationButton_id; ?>" type="button"><?php _e('Add Location', 'dak_events_wp') ?></button>
 </div>
<!-- Categories -->
 <div style="margin-top: 0.2em;">
  <span style="font-weight: bold"><?php _e('categorized as', 'dak_events_wp') ?></span>
  <div id="<?php echo $chosenCategoryList_id; ?>">
  <?php
  		if (count($instance['filter']['category_id']) > 0) {
			foreach ($categoryList as $a) {
				if (in_array($a->id, $instance['filter']['category_id'])) {
					echo '<div id="' . $chosenCategoryList_id . '-' . $a->id . '">';
					echo '<input type="hidden" name="' . $category_idName . '" value="' . $a->id . '"/>';
					echo '<span>' . $a->name . ' </span>';
					echo '<button class="dew_deleteElement" type="button"><small>' . __('Delete', 'dak_events_wo') . '</small></button>';
					echo '</div>';
				}
			}
		}
  ?>
  </div>
  <select id="<?php echo $categoryList_id; ?>">
   <?php echo $this->makeSelectOption($categoryList, 'id', 'name'); ?>
  </select>
  <button class="dew_addCategoryButton" id="<?php echo $addCategoryButton_id; ?>" type="button"><?php _e('Add Category', 'dak_events_wp') ?></button>
 </div>
		<?php
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['type'] = strip_tags( $new_instance['type'] );
		$instance['listCount'] = intval( $new_instance['listCount'] );

		if (isset($new_instance['filter'])) {
			if (!isset($instance['filter'])) {
				$instance = array();
			}

			if (!isset($new_instance['filter']['daysInFuture'])) {
				$new_instance['filter']['daysInFuture'] = 0;
			}
			$instance['filter']['daysInFuture'] =  intval( $new_instance['filter']['daysInFuture'] );

			if (isset($new_instance['filter']['arranger_id'])) {
				foreach ($new_instance['filter']['arranger_id'] as &$a) {
					$a = intval($a);
				}
			} else {
				$new_instance['filter']['arranger_id'] = array();
			}
			$instance['filter']['arranger_id'] = $new_instance['filter']['arranger_id'];

			if (isset($new_instance['filter']['location_id'])) {
				foreach ($new_instance['filter']['location_id'] as &$a) {
					$a = intval($a);
				}
			} else {
				$new_instance['filter']['location_id'] = array();
			}
			$instance['filter']['location_id'] = $new_instance['filter']['location_id'];

			if (isset($new_instance['filter']['category_id'])) {
				foreach ($new_instance['filter']['category_id'] as &$a) {
					$a = intval($a);
				}
			} else {
				$new_instance['filter']['category_id'] = array();
			}
			$instance['filter']['category_id'] = $new_instance['filter']['category_id'];
		} else {
			$instance['filter'] = array(
				'daysInFuture' => 0,
				'arranger_id' => array(),
				'location_id' => array(),
				'category_id' => array(),
			);
		}

		return $instance;
	}

	function widget($args, $instance) {
		extract($args);
		echo $before_widget;

		$calendar = new DEW_Calendar();

		if (isset($instance['title']) && !empty($instance['title'])) {
			echo $before_title . $instance['title'] . $after_title;
		}

		//if($instance['type'] == 'calendar') {
		//	$calendar->displayWidget($year, $month, array(), 2, $instance['filter'], $this->id_base);
		//} else if ($instance['type'] == 'dayCoverage') {
			
		//} else {
			if (!isset($instance['listCount'])) {
				$calendar->displayEventList(10, $instance['filter'], $this->id_base);
			} else {
				$calendar->displayEventList($instance['listCount'], $instance['filter'], $this->id_base);
			}
		//}
		echo $after_widget;
	}
}
endif;
