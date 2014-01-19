function privatesquare_trips_datepicker_init(){

    $("#arrival").datepicker({'language':'en'});
    $("#departure").datepicker({'language':'en'});

    // TO DO: on change event handler to update departure
    // when arrival changes (20140112/straup)

    // TO DO: on change event handler to ensure departure
    // is not before arrival (20140112/straup)
}

function privatesquare_trips_select2_init(){

    // TO DO: sort out FQ URL for this (20140119/straup)	 
    var geocoder = privatesquare_abs_root_url() + "user_trips_add_geocode.php";
	 
    $("#where").select2({
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

function privatesquare_trips_gather_trip_info(){

    var woeid = $("#where").val();
    var arr = $("#arrival").val();
    var dept = $("#departure").val();
    
    var arr_by = $("#arrive_by").val();
    var dept_by = $("#depart_by").val();
    
    var status = $("#status_id").val();
    var note = $("#note").val();

    var args = {
	'woeid': woeid,
	'arrival': arr,
	'departure': dept,
	'arrive_by': arr_by,
	'depart_by': dept_by,
	'status_id': status,
	'note': note
    };

    if (trip = $("#trip")){
	args['id'] = trip.val();
    }

    return args;
}

function privatesquare_trips_add_init(){

    privatesquare_trips_datepicker_init();
    privatesquare_trips_select2_init();

    $("#add-trip").submit(function(){

	var form = $("#add-trip");
	var crumb = form.attr("data-add-trip-crumb");

	var args = privatesquare_trips_gather_trip_info();
	args['crumb'] = crumb;

	var method = 'privatesquare.trips.addTrip';

	var sel = $("#select2-chosen-1");
	var name = sel.html();

	privatesquare_api_call(method, args, _privatesquare_trips_add_trip_onsuccess);
	privatesquare_set_status("Adding trip to " + htmlspecialchars(name));

	return false;
    });
}

function _privatesquare_trips_add_trip_onsuccess(rsp){

    if (rsp['stat'] != 'ok'){
	privatesquare_set_status(rsp['error']['error'], "danger");
	return false;
    }

    var url = rsp['trip']['trip_url'];
    location.href = url + "?success=1";
}

function privatesquare_trips_edit_init(){

    privatesquare_trips_datepicker_init();

    $("#trip-locality small").click(function(){
	privatesquare_trips_select2_init();
	// What to do after this... (20140118/straup)
    });

    $("#edit-trip").submit(function(){

	var form = $("#edit-trip");
	var crumb = form.attr("data-edit-trip-crumb");

	var args = privatesquare_trips_gather_trip_info();
	args['crumb'] = crumb;

	var method = 'privatesquare.trips.editTrip';

	privatesquare_api_call(method, args, _privatesquare_trips_edit_trip_onsuccess);
	privatesquare_set_status("Updating your trip");

	return false;
    });

    $("#delete-trip").click(function(){

	if (! confirm("Are you sure you want to delete this trip?")){
	    return false;
	}

	var form = $("#edit-trip");
	var crumb = form.attr("data-delete-trip-crumb");

	var trip = $("#trip");
	var trip_id = trip.val();

	var args = {
	    'id': trip_id,
	    'crumb': crumb
	};

	var method = 'privatesquare.trips.deleteTrip';

	privatesquare_api_call(method, args, _privatesquare_trips_delete_trip_onsuccess);
	privatesquare_set_status("Deleting your trip");

	return false;
    });
}

function _privatesquare_trips_edit_trip_onsuccess(rsp){

    if (rsp['stat'] != 'ok'){
	privatesquare_set_status(rsp['error']['error'], "danger");
	return false;
    }

    privatesquare_set_status("Your trip has been updated!");
}

function _privatesquare_trips_delete_trip_onsuccess(rsp){

    if (rsp['stat'] != 'ok'){
	privatesquare_set_status(rsp['error']['error'], "danger");
	return false;
    }

    var trips = privatesquare_abs_root_url() + "me/trips/?deleted=1";
    location.href = trips;
}
