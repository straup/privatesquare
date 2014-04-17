function privatesquare_trips_calendars_select2_init(){

    // http://ivaynberg.github.io/select2/

    // TO DO: sort out FQ URL for this (20140119/straup)	 
    var geocoder = privatesquare_abs_root_url() + "user_trips_add_geocode.php";
	 
    var s = $("#calendar-where").select2({
        minimumInputLength: 3,
	ajax: {
	    url: geocoder,
	    dataType: 'json',
	    data: function (term, page){
       		return {
		    q: term
		};
	    },
	    results: function(data, page){
		return {
		    results: data.results
		};
	    }
	}
    });

}

function privatesquare_trips_calendars_write_init(){

    privatesquare_trips_calendars_select2_init();

    $(".form-control").focus(function(){

	var el = $(this);
	var id = el.attr("id");

	if (id == 'calendar-expires'){
		$("#x-calendar-wrapper").show();
	}

	else {
		$("#x-calendar-wrapper").hide();
	}

    });

    /* not jquery */

    // Not doing this because I don't know what jquery is doing to 'e'
    // such that e.detail throws an error (20140308/straup)
    //$("x-calendar").bind("datetap", function(e){

    var eventsStage = document.getElementById("x-calendar-wrapper");
    var eventsCal = eventsStage.querySelector("x-calendar");

    eventsCal.addEventListener("datetap", function(e){
	var date = e.detail.date;
	var dateStr = e.detail.iso;
	$("#calendar-expires").val(dateStr);
    });

    /* we now return you to your regurlary scheduled jquery */
}

function privatesquare_trips_calendars_add_init(){

    privatesquare_trips_calendars_write_init();

    $("#calendar").submit(function(){

	var form = $("#calendar");
	var crumb = form.attr("data-calendar-crumb");

	var args = privatesquare_trips_calendars_gather_args();
	args['crumb'] = crumb;

	// console.log(args);

	var method = 'privatesquare.trips.calendars.addCalendar';

	// $("#trip").attr("disabled", "disabled");

	privatesquare_api_call(method, args, _privatesquare_trips_calendars_add_onsuccess);
	privatesquare_set_status("Adding calendar...");

	return false;
    });

}

function privatesquare_trips_calendars_edit_init(){

    privatesquare_trips_calendars_write_init();

    $("#calendar").submit(function(){

	var form = $("#calendar");
	var crumb = form.attr("data-calendar-edit-crumb");

	var btn = $(this);
	var id = btn.attr("data-calendar-id");

	var args = privatesquare_trips_calendars_gather_args();

	args['id'] = id;
	args['crumb'] = crumb;

	// console.log(args);

	var method = 'privatesquare.trips.calendars.editCalendar';
	privatesquare_api_call(method, args, _privatesquare_trips_calendars_edit_onsuccess);

	privatesquare_set_status("Updating your calendar...");
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

	privatesquare_set_status("Deleting your calendar now...");
	return false;
    });
}

function privatesquare_trips_calendars_gather_args(){

    var args = {}

    var name = $("#calendar-name");
    args['name'] = name.val();

    var where = $("#calendar-where");
    args['woeid']= where.val();

    var note = $("#calendar-notes");
    args['note'] = note.val();

    var expires = $("#calendar-expires");
    args['expires'] = expires.val();

    var status = $("#calendar-trip-status");
    args['status_id'] = status.val();

    var include_notes = $("#calendar-include-notes");
    args['include_notes'] = (include_notes.attr("checked")) ? 1 : 0;
    
    var past_trips = $("#calendar-past-trips");
    args['past_trips'] = (past_trips.attr("checked")) ? 1 : 0;
    
    return args;
}

function _privatesquare_trips_calendars_add_onsuccess(rsp){

    if (rsp['stat'] != 'ok'){
	privatesquare_api_error(rsp);
	return false;
    }

    privatesquare_set_status("Boom! Your calendar has been created.");
}

function _privatesquare_trips_calendars_edit_onsuccess(rsp){

    if (rsp['stat'] != 'ok'){
	privatesquare_api_error(rsp);
	return false;
    }

    privatesquare_set_status("Okay! Your calendar has been updated.");
}

function _privatesquare_trips_calendars_delete_onsuccess(rsp){

    if (rsp['stat'] != 'ok'){
	privatesquare_api_error(rsp);
	return false;
    }

    var loc = privatesquare_abs_root_url() + 'me/trips/calendars/?deleted=1';
    location.href = loc;
}
