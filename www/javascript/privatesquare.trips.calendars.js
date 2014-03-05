function privatesquare_trips_calendars_add_init(){

    $("#calendar").submit(function(){

	var form = $("#calendar");
	var crumb = form.attr("data-calendar-crumb");

	var args = privatesquare_trips_calendars_gather_args();
	args['crumb'] = crumb;

	console.log(args);

	var method = 'privatesquare.trips.calendars.addCalendar';

	// $("#trip").attr("disabled", "disabled");

	privatesquare_api_call(method, args, _privatesquare_trips_calendars_add_onsuccess);
	privatesquare_set_status("Adding calendar...");

	return false;
    });

}

function privatesquare_trips_calendars_edit_init(){

    $("#calendar").submit(function(){

	console.log("hi");
	return false;

	var form = $("#calendar");
	var crumb = form.attr("data-calendar-crumb");

	var args = privatesquare_trips_calendars_gather_args();
	console.log(args);

	var method = 'privatesquare.trips.calendars.deleteCalendar';

	privatesquare_api_call(method, args, _privatesquare_trips_calendars_delete_onsuccess);
	return false;
    });

    $("#calendar-delete").click(function(){

	var btn = $(this);
	var id = btn.attr("data-calendar-id");
	var crumb = btn.attr("data-calendar-delete-crumb");

	var args = {
	    'id': id,
	    'crumb': crumb
	};

	var method = 'privatesquare.trips.calendars.deleteCalendar';
	privatesquare_api_call(method, args, _privatesquare_trips_calendars_delete_onsuccess);

	privatesquare_set_status("Deleting your calendar now.");
	return false;
    });
}

function privatesquare_trips_calendars_gather_args(){

	var note = $("#calendar-notes");
	note = note.val();

	// THESE ARE WRONG (20140215/straup)

	var include_notes = $("#calendar-include-notes");
	include_notes = include_notes.val();

	var past_trips = $("#calendar-past-trips");
	past_trips = past_trips.val();

	var args = {
	    'note': note,
	    'include_notes': include_notes,
	    'past_trips': past_trips
	};

	return args;
}

function _privatesquare_trips_calendars_add_onsuccess(rsp){
    console.log(rsp);
}

function _privatesquare_trips_calendars_edit_onsuccess(rsp){
    console.log(rsp);
}

function _privatesquare_trips_calendars_delete_onsuccess(rsp){

    if (rsp['stat'] != 'ok'){
	privatesquare_api_error(rsp);
	return false;
    }

    var loc = privatesquare_abs_root_url() + 'me/trips/calendars/?deleted=1';
    location.href = loc;
}
