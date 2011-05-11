// Javascript functions for interacting with event list

jQuery(document).ready(function(){
  jQuery('.dew_showEvent').click(function(){
    //alert('hello');
    jQuery(this).toggleClass('dew_eventToggled');
    jQuery(this).parent().children('.dew_eventElem').slideToggle('fast');
  });
  
  jQuery('.dew_agenda .agenda_read_more').click(function(){
    jQuery(this).next('.agenda_description').slideToggle('slow');
  });
  
  jQuery('.dew_agenda .agenda_collection_name').click(function() {
    jQuery(this).toggleClass('dew_active');
    jQuery(this).next('.agenda_event_collection').toggleClass('dew_hide');
  });
});
