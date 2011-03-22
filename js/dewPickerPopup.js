var dewpp; // dewPickerPopup

(function($) {

	var ed, inputs = {}, list, activeSelection, activeSelectionId = null, srvUrl, 
	    state = {};

	dewpp = {

		init : function () {
			inputs.addElement = $('#pickerPopupAddElement');
			inputs.loadMoreElements = $('#pickerPopupLoadMoreElements');
			list = $('#pickerPopupList');

			activeSelection = $('#pickerPopupList li');
			activeSelection.live('click', dewpp.setActiveSelection );

			inputs.addElement.click( function() {
				dewpp.update();
			});

			inputs.loadMoreElements.click(dewpp.loadMoreElements);

			// dewEventServerUrl is a variable loaded through the dak_events_wp
			srvUrl = dewEventServerUrl;

			state.limit = 20;
			state.offset = 0;
			state.count = 0;
			state.totalCount = 0;

			dewpp.loadElements(true, dewpp.canLoadMoreElements);
		},

		update : function () {
			ed = tinyMCEPopup.editor;

			if (activeSelectionId != null) {
				ed.execCommand('mceInsertContent', 0, '[dew_event_detailbox id=' + activeSelectionId + ']');
			}
		},

		loadMoreElements : function () {
			state.offset = state.offset + state.limit;
			dewpp.loadElements(false, dewpp.canLoadMoreElements);
		},

		canLoadMoreElements : function () {
			if ((state.offset + state.limit) < state.totalCount) {
				inputs.loadMoreElements.attr('disabled', false);
			} else {
				inputs.loadMoreElements.attr('disabled', true);
			}
		},

		loadElements : function (emptyList, callback) {
			var url = srvUrl + 'api/json/filteredEvents';

			var data = {};

			if ((typeof(emptyList) == 'boolean') && (emptyList == true)) {
				state.offset = 0;
				state.count = 0;
				state.totalCount = 0;
			}

			data.offset = state.offset;
			data.limit = state.limit;

			$.getJSON( url + '?callback=?', data, function (response) {
				if (response.count > 0) {
					if ((typeof(emptyList) == 'boolean') && (emptyList == true)) {
						list.empty();
					}

					state.limit = response.limit;
					state.offset = response.offset;
					state.count = response.count;
					state.totalCount = response.totalCount;

					for (var i in response.data) {
						var e = response.data[i];
						var location;
						
						if (e.location_id > 0) {
							location = e.commonLocation.name;
						} else {
							location = e.customLocation;
						}

						list.append(
						  '<li id="pickerPopupList-' + e.id + '"><span class="title">' + e.title + '</span><br /><small>' + 
						  e.startDate + ' ' + e.startTime + ' ' + location + '</small></li>'
						);
					}
				}

				if (typeof(callback) != 'undefined') {
					callback();
				}
			});
		},

		setActiveSelection : function() {
			if (activeSelectionId != null) {
				$('#pickerPopupList-' + activeSelectionId).removeClass('selected');
			} else {
				inputs.addElement.attr('disabled', false);
			}

			activeSelectionId = $(this).attr('id').substring(16);

			//alert(activeSelectionId);

			$('#pickerPopupList-' + activeSelectionId).addClass('selected');
		}
	};
	
	$(document).ready( dewpp.init );
})(jQuery);
