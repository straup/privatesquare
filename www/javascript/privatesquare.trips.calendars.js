function privatesquare_trips_calendars_add_init(){

    $("#add-calendar").submit(function(){

	var form = $("#add-calendar");
	var crumb = form.attr("data-add-calendar-crumb");

	var note = $("#add-calendar-notes");
	note = note.val();

	// THESE ARE WRONG (20140215/straup)

	var include_notes = $("#add-calendar-include-notes");
	include_notes = include_notes.val();

	var past_trips = $("#add-calendar-past-trips");
	past_trips = past_trips.val();

	var args = {
	    'crumb': crumb,
	    'note': note,
	    'include_notes': include_notes,
	    'past_trips': past_trips,
	};
	
	console.log(args);

	var method = 'privatesquare.trips.calendars.addCalendar';

	// $("#add-trip").attr("disabled", "disabled");

	privatesquare_api_call(method, args, _privatesquare_trips_calendars_add_onsuccess);
	privatesquare_set_status("Adding calendar...");

	return false;
    });

}

function _privatesquare_trips_calendars_add_onsuccess(rsp){
    console.log(rsp);
}
