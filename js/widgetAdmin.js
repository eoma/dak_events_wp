// Admin function for events calendar widget


function addChosenElemFromList (root_id, listType) {
  var listTypeFirstUpper = listType.substr(0, 1).toUpperCase() + listType.substr(1);

  var selected_id = jQuery("select#" + root_id + listType + "List :selected").val();
  var selectedText = jQuery("select#" + root_id + listType + "List :selected").text();

  var elem_id = root_id + 'chosen' + listTypeFirstUpper + 'List-' + selected_id;
  var baseName = jQuery( 'input#' + root_id + 'base').attr('value');
  baseName = baseName.substring(0, baseName.length - 1);
  if (typeof(jQuery('#' + elem_id).attr('id')) == 'undefined') {
    var div = jQuery("<div id='" + elem_id + "'></div>");

    jQuery(div).append("<input type='hidden' name='" + baseName + "filter][" + listType + "_id][]' value='" + selected_id + "'/>");
    jQuery(div).append("<span>" + selectedText + " </span>");
    var delButton = jQuery(div).append("<button class='dew_deleteElement' type='button'><small>Delete</small></button>");
    jQuery('div#' + root_id + 'chosen' + listTypeFirstUpper + 'List').append(div);
  } else {
    alert(selectedText + ' is already chosen');
  }
}

jQuery(document).ready(function() {
  jQuery("button.dew_deleteElement").live('click', function () {
    jQuery(this).parent().empty().remove();
  });

  jQuery("select.dew_type").live('change', function() {
    var id = jQuery(this).attr('id');
    var root_id = id.substring(0, id.length - 4);
    //alert(root_id);
    if ( "list" == this.value ) {
      jQuery("#" + root_id + 'eventListOptions' ).show();
    } else {
      jQuery("#" + root_id + 'eventListOptions' ).hide();
    }
  });

  jQuery("button.dew_addArrangerButton").live('click', function() {
    var id = jQuery(this).attr('id');
    var root_id = id.substring(0, id.length - 17);

    addChosenElemFromList(root_id, 'arranger');
  });

  jQuery("button.dew_addLocationButton").live('click', function() {
    var id = jQuery(this).attr('id');
    var root_id = id.substring(0, id.length - 17);

    addChosenElemFromList(root_id, 'location');
  });

  jQuery("button.dew_addCategoryButton").live('click', function() {
    var id = jQuery(this).attr('id');
    var root_id = id.substring(0, id.length - 17);

    addChosenElemFromList(root_id, 'category');
  });
});
