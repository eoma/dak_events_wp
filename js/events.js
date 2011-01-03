// Javascript functions for interacting with event list

jQuery(document).ready(function(){
  jQuery('.dew_showEvent').click(function(){
    //alert('hello');
    jQuery(this).toggleClass('dew_eventToggled');
    jQuery(this).parent().children('.dew_eventElem').slideToggle('fast');
  });
  
  jQuery('.dew_agenda h2').click(function(){
    jQuery(this).next('.event_date_list').slideToggle('fast');
  })
});
