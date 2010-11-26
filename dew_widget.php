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

	function __construct() {
		$options = get_option('optionsDakEventsWp');
		$this->eventServerUrl = $options['eventServerUrl'];
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
		if (empty($instance['listCount'])) $instance['listCount'] = 5;
		if (empty($instance['filter'])) $instance['filter'] = array();
		if (empty($instance['filter']['arranger_id'])) $instance['filter']['arranger_id'] = array();
		if (empty($instance['filter']['location_id'])) $instance['filter']['location_id'] = array();
		if (empty($instance['filter']['category_id'])) $instance['filter']['category_id'] = array();

		$client = new eventsCalendarClient($this->eventServerUrl);

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
  <label for="<?php echo $title_id; ?>">Title:
   <input type='text' id='<?php echo $title_id; ?>' name='<?php echo $titleName; ?>' value='<?php echo $title; ?>'/>
  </label>
 </p>
 <p style="text-align:center;">
  <label for="<?php echo $type_id; ?>">
   Calendar Type:
   <select class="dew_type" name="<?php echo $typeName; ?>" id="<?php echo $type_id; ?>">
    <option value="calendar">Calendar</option>
    <option value="list" <?php if ( isset($instance['type']) && 'list' == $instance['type'] ) echo 'selected="selected"'; ?>>Event List</option>
   </select>
  </label>
 </p>
 <div id="<?php echo $eventListOptions_id; ?>" style="<?php if ( 'list' != $instance['type'] ) echo 'display: none;'; ?>">
  <p>
   <span style="font-weight: bold">Event List options</span>
  </p>
  <p>
   <label for="<?php echo $listCount_id; ?>">
    Number of events:
    <input style="width: 30px;" type="text" id="<?php echo $listCount_id; ?>" name="<?php echo $listCountName; ?>" value="<?php echo $instance['listCount']; ?>" />
   </label>
  </p>
 </div>
 <span>Requires events to be</span>
<!-- Arrangers -->
 <div style="margin-top: 0.2em;">
  <span style="font-weight: bold">arranged by</span>
  <div id="<?php echo $chosenArrangerList_id; ?>">
  <?php
  		if (count($instance['filter']['arranger_id']) > 0) {
			foreach ($arrangerList as $a) {
				if (in_array($a->id, $instance['filter']['arranger_id'])) {
					echo '<div id="' . $chosenArrangerList_id . '-' . $a->id . '">';
					echo '<input type="hidden" name="' . $arranger_idName . '" value="' . $a->id . '"/>';
					echo '<span>' . $a->name . ' </span>';
					echo '<button class="dew_deleteElement" type="button"><small>Delete</small></button>';
					echo '</div>';
				}
			}
		}
  ?>
  </div>
  <select id="<?php echo $arrangerList_id; ?>">
   <?php echo $this->makeSelectOption($arrangerList, 'id', 'name'); ?>
  </select>
  <button class="dew_addArrangerButton" id="<?php echo $addArrangerButton_id; ?>" type="button">Add arranger</button>
 </div>
<!-- Locations -->
 <div style="margin-top: 0.2em;">
  <span style="font-weight: bold">located at</span>
  <div id="<?php echo $chosenLocationList_id; ?>">
  <?php
  		if (count($instance['filter']['location_id']) > 0) {
			foreach ($locationList as $a) {
				if (in_array($a->id, $instance['filter']['location_id'])) {
					echo '<div id="' . $chosenLocationList_id . '-' . $a->id . '">';
					echo '<input type="hidden" name="' . $location_idName . '" value="' . $a->id . '"/>';
					echo '<span>' . $a->name . ' </span>';
					echo '<button class="dew_deleteElement" type="button"><small>Delete</small></button>';
					echo '</div>';
				}
			}
		}
  ?>
  </div>
  <select id="<?php echo $locationList_id; ?>">
   <?php echo $this->makeSelectOption($locationList, 'id', 'name'); ?>
  </select>
  <button class="dew_addLocationButton" id="<?php echo $addLocationButton_id; ?>" type="button">Add Location</button>
 </div>
<!-- Categories -->
 <div style="margin-top: 0.2em;">
  <span style="font-weight: bold">categorized as</span>
  <div id="<?php echo $chosenCategoryList_id; ?>">
  <?php
  		if (count($instance['filter']['category_id']) > 0) {
			foreach ($categoryList as $a) {
				if (in_array($a->id, $instance['filter']['category_id'])) {
					echo '<div id="' . $chosenCategoryList_id . '-' . $a->id . '">';
					echo '<input type="hidden" name="' . $category_idName . '" value="' . $a->id . '"/>';
					echo '<span>' . $a->name . ' </span>';
					echo '<button class="dew_deleteElement" type="button"><small>Delete</small></button>';
					echo '</div>';
				}
			}
		}
  ?>
  </div>
  <select id="<?php echo $categoryList_id; ?>">
   <?php echo $this->makeSelectOption($categoryList, 'id', 'name'); ?>
  </select>
  <button class="dew_addCategoryButton" id="<?php echo $addCategoryButton_id; ?>" type="button">Add Category</button>
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

		if ( is_active_widget(false, false, $this->id_base, true) ) {
			wp_enqueue_script('dew_js_events', DEW_URL . '/js/events.js', array('jquery'));
		}

		if (isset($instance['title']) && !empty($instance['title'])) {
			echo $before_title . $instance['title'] . $after_title;
		}

		//if($instance['type'] == 'calendar') {
		//	$calendar->displayWidget($year, $month, array(), 2, $instance['filter'], $this->id_base);
		//} else if ($instance['type'] == 'dayCoverage') {
			
		//} else {
			if (!isset($instance['listCount'])) {
				$calendar->displayEventList(5, $instance['filter'], $this->id_base );
			} else {
				$calendar->displayEventList($instance['listCount'], $instance['filter'], $this->id_base);
			}
		//}
		echo $after_widget;
	}
}
endif;
