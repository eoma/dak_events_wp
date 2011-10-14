<?php

/**
 * This file contains sample code for rendering agenda,
 * events, festivals, etc.
 *
 * To override a render action with your own template
 * you have to first call remove_all_actions('dew_render_some_template_name')
 * (eg. remove_all_actions('dew_render_agenda')) and then register your new
 * template function by calling 
 * add_action('dew_render_some_template_name', 'some_function_name', 10, 1).
 * The last parameter should equal the same number as the render action you
 * are trying to replace.
 *
 * It is probably a good practice to start defining your own render actions
 * by copying one defined here and then tweak the function.
 *
 * Learn more at http://codex.wordpress.org/Function_Reference/add_action
 */

// Agenda menu
add_action('dew_render_agenda_menu', 'DEW_template::agendaMenu', 10, 1);

// The following actions are related to rendering of agendas

// This one is the primary template to be called from the dew_agenda_shortcode_handler()
add_action('dew_render_agenda', 'DEW_template::agenda', 10, 2); 

// These are primarily called from do_action('dew_render_agenda', ...) 
// and do_action('dew_render_fullfestival', ...)
add_action('dew_render_agenda_date_collection', 'DEW_template::agendaEventDateCollection', 10, 2);
add_action('dew_render_agenda_full_event', 'DEW_template::agendaFullEvent', 10, 1);
add_action('dew_render_agenda_compact_event', 'DEW_template::agendaCompactEvent', 10, 1);

// Archive list
add_action('dew_render_archive_list', 'DEW_template::archiveList', 10, 1);

// Full events and festivals
add_action('dew_render_fullevent', 'DEW_template::fullEvent', 10, 1);
add_action('dew_render_fullfestival', 'DEW_template::fullFestival', 10, 2);

// Detail boxes
add_action('dew_render_event_detailbox', 'DEW_template::eventDetailBox', 10, 1);
add_action('dew_render_festival_detailbox', 'DEW_template::festivalDetailBox', 10, 1);

// Widget
add_action('dew_render_widget_list', 'DEW_template::widgetList', 10, 2);
add_action('dew_render_widget_event', 'DEW_template::widgetEvent', 10, 1);

class DEW_template {

	static public function widgetList ($dateSortedEvents, $config) {
		?>
<ul class="dew_eventList" id="<?php echo $config['id_base'] ?>-dak-events-wp-list">

<?php

		foreach ($dateSortedEvents as $timestamp => $rawEvents) {
			$dayName = ucfirst(date_i18n('l', $timestamp ));
			$date = date($config['dateFormat'], $timestamp);

			echo '<li class="dew_eventList_date">' . $dayName . ' ' . $date . '</li>' . "\n";

			foreach ($rawEvents as $rawEvent) {
				echo '<li class="dew_event" id="' . $config['id_base'] . '-dak-events-wp-list-' . $rawEvent->id . '">';

				do_action('dew_render_widget_event', $rawEvent);

				echo'</li>' . "\n";
			}
		}

?>

</ul>
		<?php
	}

	static public function widgetEvent ($rawEvent) {
		$event = new DEW_event($rawEvent);

		$googleCalUrl = DEW_tools::createGoogleCalUrl($rawEvent);

		$startTime = date('H:i', $event->getStartTimestamp());

		$duration = $event->getFormattedDuration();

		?>

<div class="dew_showEvent">
  <span class="event_name"><?php echo $event->getTitle() ?></span>
  <span class="location_name">
    <?php echo $event->getCategory() ?> - <?php echo $event->getLocation() ?>
    <span class="event_time"><?php echo $startTime ?></span>
  </span>
</div>
<div class="dew_eventElem dew_hide">
  <div class="dew_content">
    <?php echo $event->getLeadParagraph() ?>
  </div>
  <div class="dew_data">
    <strong><?php _e('When:', 'dak_events_wp') ?></strong> <?php echo $duration ?><br />
    <strong><?php _e('Where:', 'dak_events_wp') ?></strong> <?php echo $event->getLocation() ?><br />
    <strong><?php _e('Arranger:', 'dak_events_wp') ?></strong> <?php echo $event->getArranger() ?><br />
    <strong><?php _e('Type:', 'dak_events_wp') ?></strong> <?php echo $event->getCategory() ?><br />

	<?php if ($event->hasCoverCharge()): ?>
    <strong><?php _e('Covercharge:', 'dak_events_wp') ?></strong> <?php echo $event->getCoverCharge() ?><br />
	<?php endif ?>

	<?php if ($event->hasFestival()): ?>
    <?php 
      printf(__('Part of festival %s', 'dak_events_wp'), 
      '<a href="' . $event->getFestivalUrl() . '">' . $event->getFestivalTitle() . ' ' . date('d.m.Y', $event->getFestivalStartTimestamp()) . '</a>') 
    ?><br />
	<?php endif ?>

    <a href="<?php echo $event->getUrl() ?>"><?php _e('Read more', 'dak_events_wp') ?></a>
  </div>
</div>

		<?php
	}

	static public function agendaMenu ($menu) {
		?>
<ul class="agenda_menu">
 <li <?php if ($menu['upcoming']['active']) echo 'class="active"' ?>>
  <a href="<?php echo $menu['upcoming']['url'] ?>"><?php echo $menu['upcoming']['linkText'] ?></a>
 </li>
 
<?php foreach ($menu['nextMonths'] as $m): ?>
 <li <?php if ($m['active']) echo 'class="active"' ?>>
  <a href="<?php echo $m['url'] ?>"><?php echo $m['linkText'] ?></a>
 </li>
<?php endforeach ?>

 <li <?php if ($menu['archive']['active']) echo 'class="active"' ?>>
  <a href="<?php echo $menu['archive']['url'] ?>"><?php echo $menu['archive']['linkText'] ?></a>
 </li>
</ul>
		<?php
	}

	static public function agenda (array $dateSortedEvents, $config = array()) {

		if ( ! empty($config['title']) ) {
			echo "<h2>{$config['title']}</h2><br />\n";
		}

		echo "<div class=\"dew_agenda\">\n";

		if (empty($dateSortedEvents)) {
			echo "<p>" . __('Could not find any events :(', 'dak_events_wp') . "</p>\n";
		} else {
			foreach ($dateSortedEvents as $timestamp => $events) {
				do_action('dew_render_agenda_date_collection', $events, $timestamp);
			}
		}

		echo "</div>\n";
	}

	static public function agendaEventDateCollection ($events, $timestamp) {
		// Will contain all events in a day

		$dayName = date_i18n('l', $timestamp);
		$dayNumber = date('j', $timestamp);
		$monthName = date_i18n('F', $timestamp);

		//var_dump($events);
		
		// Fix for weird behaviour by wordpress' do_action behaviour.
		// It seems to strip away the array if it only contains a single element
		if ( ! is_array($events) ) {
			$events = array($events);
		}

		?>
<div class='agenda_day clearfix'>
  <h2 class='agenda_box'>
    <span class='agenda_day_name'><?php echo $dayName ?></span>
    <span class='agenda_day_number'><?php echo $dayNumber ?></span>
    <span class='agenda_month_name'><?php echo $monthName ?></span>
  </h2>
  <div class='event_date_list'>
    <?php

    foreach ($events as $event) {
        do_action('dew_render_agenda_compact_event', $event);
	}

	?>
  </div>
</div>

<?php

	}

	static public function agendaCompactEvent($rawEvent) {
		$event = new DEW_event($rawEvent);

		$startTime = date('H:i', $event->getStartTimestamp());
		?>

<div class='agenda_compact_event_wrapper'>
 <a href="<?php echo $event->getUrl() ?>"><?php echo $event->getTitle() ?></a> <br />
 <span class="agenda_compact_event_details">
  <?php echo $startTime ?> - <?php echo $event->getCategory() ?> - <?php echo $event->getLocation() ?><?php if ($event->happensNow()) echo " - <em>" . __("happens now", 'dak_events_wp') . "</em>"; ?>
 </span>
</div>

		<?php
	}

	static public function agendaFullEvent($rawEvent) {
		$event = new DEW_event($rawEvent);

		$startTime = date('H:i', $event->getStartTimestamp());

		$duration = $event->getFormattedDuration();

		?>

<div class="agenda_event_wrapper">
  <h3><a href="<?php echo $event->getUrl() ?>"><?php echo $event->getTitle() ?></a></h3>
  <p class="agenda_data">
    <?php printf(__('%s in %s', 'dak_events_wp') , $event->getCategories(), $event->getLocation()) ?><br />
    <?php echo __('When:', 'dak_events_wp') . ' ' . $duration ?><br />
    <?php echo __('Arranger:', 'dak_events_wp') . ' ' . $event->getArranger() ?><br />

	<?php if ($event->hasCoverCharge()): ?>
    <?php echo __('Covercharge:', 'dak_events_wp') . ' ' . $event->getCoverCharge()  ?><br />
	<?php endif ?>

	<?php if ($event->hasFestival()): ?>
    <?php 
      printf(__('Part of festival %s', 'dak_events_wp'), 
      '<a href="' . $event->getFestivalUrl() . '">' . $event->getFestivalTitle() . ' ' . date('d.m.Y', $event->getFestivalStartTimestamp()) . '</a>') 
    ?><br />
	<?php endif ?>

  </p>
  <p><?php echo $event->getLeadParagraph() ?></p>
</div>

		<?php
	}

	static public function archiveList ($archiveList) {
		$previousYear = null;
		$yearElements = null;
		
		$content = '<ul class="agenda_archive">' . "\n";

		foreach (array_reverse($archiveList) as $h) {
			$ts = strtotime($h->date);
			$year = date('Y', $ts);
			$month = date('n', $ts);

			if ($year != $previousYear) {
					
				if ( ! empty($yearElements) ) {
					$content .= ' <li><span class="agenda_archive_year">' . $previousYear . '</span>' . "\n";
					$content .= '  <ul>' . "\n";
					$content .= $yearElements . "\n";
					$content .= '  </ul>' . "\n";
					$content .= ' </li>' . "\n";
				}
					
				$previousYear = $year;
				$yearElements = '';
			}

			$agendaLink = DEW_tools::generateLinkToAgenda('month', array($year, $month));
			$yearElements .= '   <li><a href="' . $agendaLink . '">' . date_i18n('F', $ts) . '</a></li>' . "\n";
		}

		$content .= ' <li><span class="agenda_archive_year">' . $previousYear . '</span>' . "\n";
		$content .= '  <ul>' . "\n";
		$content .= $yearElements . "\n";
		$content .= '  </ul>' . "\n";
		$content .= ' </li>' . "\n";
		$content .= '</ul>' . "\n";

		echo $content;
	}

	static public function fullEvent($rawEvent, array $config = array()) {
		// All named arguments are required in the format

		/**
		 * $config can be an associative array
		 * array(
		 * 	'no_title' => bool, // Whether to include the title or not
		 * 	'exclude_metadata' => bool, // Whether to include the metadata (start/end, location, etc) or not
		 * )
		 */

		$event = new DEW_event($rawEvent);

		$googleCalUrl = DEW_tools::createGoogleCalUrl($rawEvent);

		$title = '<h2>' . $event->getTitle() . '</h2>';
		if (isset($config['no_title']) && ($config['no_title'] == true)) {
			$title = '';
		}

		?>
<div class="agenda_event_wrapper">
  <?php echo $title ?>

  <?php if (!isset($config['exclude_metadata']) || !$config['exclude_metadata']): ?>
  
  <p class="agenda_data">
    <?php printf(__('%s in %s', 'dak_events_wp') , $event->getCategory(), $event->getLocation()) ?><br />
    <?php echo __('Starts:', 'dak_events_wp') . ' ' . date_i18n('H:i, j F Y', $event->getStartTimestamp()) ?><br />
    <?php echo __('Ends:', 'dak_events_wp') . ' ' . date_i18n('H:i, j F Y', $event->getEndTimestamp()) ?><br />
    <?php echo __('Arranger:', 'dak_events_wp') . ' ' . $event->getArranger()  ?><br />

	<?php if ($event->hasCoverCharge()): ?>
    <?php echo __('Covercharge:', 'dak_events_wp') . ' ' . $event->getCoverCharge()  ?><br />
	<?php endif ?>

	<?php if ($event->hasFestival()): ?>
    <?php 
      printf(__('Part of festival %s', 'dak_events_wp'), 
      '<a href="' . $event->getFestivalUrl() . '">' . $event->getFestivalTitle() . ' ' . date('d.m.Y', $event->getFestivalStartTimestamp()) . '</a>') 
    ?><br />
	<?php endif ?>

    <a href="<?php echo $event->getICalUrl() ?>"><?php _e('Add event to your calendar', 'dak_events_wp') ?></a>
    <a href="<?php echo $googleCalUrl ?>" target="_blank"><?php _e('Google calendar', 'dak_events_wp') ?></a>
  </p>

  <?php endif // end if !exclude_metadata ?>

  <p><?php echo $event->getLeadParagraph() ?></p>
  <?php echo $event->getDescription() ?>

  <?php if ($event->hasPrimaryPicture()): ?>
  <img src="<?php echo DEW_tools::getPictureUrl($event->getPrimaryPicture()) ?>" alt="<?php echo $event->getPrimaryPicture()->description ?>" />
  <?php endif ?>

  <p><small><a href="<?php echo $event->getUrl(false) ?>"><?php _e('Orginal event', 'dak_events_wp') ?></a></small></p>
</div>
		<?php

	}

	static public function fullFestival($rawFestival, $dateSortedEvents, array $config = array()) {
		// All named arguments are required in the format

		/**
		 * $config can be an associative array
		 * array(
		 * 	'no_title' => bool, // Whether to include the title or not
		 * 	'exclude_metadata' => bool, // Whether to include the metadata (start/end, location, etc) or not
		 * )
		 */

		$festival = new DEW_festival($rawFestival);

		$title = '<h2>' . $festival->getTitle() . '</h2>';
		if (isset($config['no_title']) && ($config['no_title'] == true)) {
			$title = '';
		}

		$googleCalUrl = DEW_tools::createGoogleCalUrl($rawFestival);

		?>
<div class="agenda_event_wrapper">
  <?php echo $title ?>

  <?php if (!isset($config['exclude_metadata']) || !$config['exclude_metadata']): ?>

  <p class="agenda_data">
    <?php echo __('Where;', 'dak_events_wp') . ' ' . $festival->getLocation() ?><br />
    <?php echo __('When:', 'dak_events_wp') . ' ' . $festival->getFormattedDuration() ?><br />
    <?php echo __('Arranger:', 'dak_events_wp') . ' ' . $festival->getArranger() ?><br />

	<?php if ($festival->hasCoverCharge()): ?>
    <?php echo __('Covercharge:', 'dak_events_wp') . ' ' . $festival->getCoverCharge()  ?><br />
	<?php endif ?>

    <a href="<?php echo $festival->getICalUrl() ?>"><?php _e('Add festival to your calendar', 'dak_events_wp') ?></a>
    <a href="<?php echo $googleCalUrl ?>" target="_blank"><?php _e('Google calendar', 'dak_events_wp') ?></a>
    <br />
    <a href="#dew_festivalEvents"><?php _e('Jump to the events', 'dak_events_wp') ?></a>
  </p>

  <?php endif ?>

  <p><?php echo $festival->getLeadParagraph() ?></p>
  <?php echo $festival->getDescription() ?>

  <div id='dew_festivalEvents'>
   <?php do_action('dew_render_agenda', $dateSortedEvents) ?>
  </div>

  <p><small><a href="<?php echo $festival->getUrl(false) ?>"><?php _e('Orginal festival', 'dak_events_wp') ?></a></small></p>
</div>

		<?php
	}

	static public function eventDetailBox($rawEvent) {
		/*
		 * All named arguments are required in the format
		 */
		$event = new DEW_event($rawEvent);

		$startTime = date('H:m', $event->getStartTimestamp());

		if ($event->endSameDay()) {
			$end = date_i18n(__('H:i'), $event->getEndTimestamp());
		} else {
			$end = date_i18n(__('F j, Y H:i'), $event->getEndTimestamp());
		}

		$googleCalUrl = DEW_tools::createGoogleCalUrl($rawEvent);

		?>

<div class='dew_eventDetailBox'>
  <div class='dew_eventDate'>
    <span class='agenda_day_name'><?php echo date_i18n('l', $event->getStartTimestamp) ?></span>
    <span class='agenda_day_number'><?php echo date('j', $event->getStartTimestamp) ?></span>
    <span class='agenda_month_name'><?php echo date_i18n('F', $event->getStartTimestamp) ?></span>
  </div>
  <div class='dew_eventDetails'>
    <span class='dew_eventTitle'>
      <a href="<?php echo $event->getUrl() ?>"><?php echo $event->getTitle ?></a>
    </span><br />

    <?php printf(__('%s in %s', 'dak_events_wp'), $event->getCategory, $event->getLocation()) ?><br />
    <?php echo __('Starts', 'dak_events_wp') . " " . $startTime ?><br />
    <?php echo __('Ends', 'dak_events_wp') . " " . $end ?><br />
    <?php echo __('Arranged by', 'dak_events_wp') . " " . $event->getArranger() ?><br />

	<?php if ($event->hasCoverCharge()): ?>
    <?php echo __('Covercharge:', 'dak_events_wp') . ' ' . $event->getCoverCharge()  ?><br />
	<?php endif ?>

	<?php if ($event->hasFestival()): ?>
    <?php 
      printf(__('Part of festival %s', 'dak_events_wp'), 
      '<a href="' . $event->getFestivalUrl() . '">' . $event->getFestivalTitle() . ' ' . date('d.m.Y', $event->getFestivalStartTimestamp()) . '</a>') 
    ?><br />
	<?php endif ?>

    <a href="<?php echo $event->getICalUrl() ?>"><?php _e('Add event to your calendar', 'dak_events_wp') ?></a>
    <a href="<?php echo $googleCalUrl ?>" target="_blank"><?php _e('Google calendar', 'dak_events_wp') ?></a>
  </div>
</div>

		<?php

	}

	static public function festivalDetailBox($rawFestival, $config = array()) {
		$festival = new DEW_festival($rawFestival);

		$startTime = date('H:m', $festival->getStartTimestamp());

		if ($festival->endSameDay()) {
			$end = date_i18n(__('H:i'), $festival->getEndTimestamp());
		} else {
			$end = date_i18n(__('F j, Y H:i'), $festival->getEndTimestamp());
		}

		$googleCalUrl = DEW_tools::createGoogleCalUrl($rawFestival);

		?>

<div class='dew_eventDetailBox'>
  <div class='dew_eventDate'>
    <span class='agenda_day_name'><?php echo date_i18n('l', $festival->getStartTimestamp()) ?></span>
    <span class='agenda_day_number'><?php echo date('j', $festival->getStartTimestamp()) ?></span>
    <span class='agenda_month_name'><?php echo date_i18n('F', $festival->getStartTimestamp()) ?></span>
  </div>
  <div class='dew_eventDetails'>
    <span class='dew_eventTitle'><a href='<?php echo $festival->getUrl() ?>'><?php echo $festival->getTitle() ?></a></span><br />
    <?php echo $festival->getLocation() ?><br />
    <?php echo __('Starts', 'dak_events_wp') . " " . $startTime ?><br />
    <?php echo __('Ends', 'dak_events_wp') . " " . $end ?><br />
    <?php echo __('Arranged by', 'dak_events_wp') . " " . $festival->getArranger() ?><br />

	<?php if ($festival->hasCoverCharge()): ?>
    <?php echo __('Covercharge:', 'dak_events_wp') . ' ' . $festival->getCoverCharge()  ?><br />
	<?php endif ?>

    <a href="<?php echo $festival->getICalUrl() ?>"><?php _e('Add festival to your calendar', 'dak_events_wp') ?></a>
    <a href="<?php echo $googleCalUrl ?>" target="_blank"><?php _e('Google calendar', 'dak_events_wp') ?></a>
  </div>
</div>

		<?php

	}
}
