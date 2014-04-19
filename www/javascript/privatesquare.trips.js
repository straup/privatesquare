function privatesquare_trips_datepicker_init(){

    // x-calendar stuff

    $(".form-control").focus(function(){

	var el = $(this);
	var id = el.attr("id");

	if (id == 'arrival'){
	    $("#x-calendar-arrival-wrapper").show();
	    $("#x-calendar-departure-wrapper").hide();
	}

	else if (id == 'departure'){
	    $("#x-calendar-arrival-wrapper").hide();
	    $("#x-calendar-departure-wrapper").show();
	}

	else {
	    $("#x-calendar-arrival-wrapper").hide();
	    $("#x-calendar-departure-wrapper").hide();
	}

	// Note that we handle the select2 menu (for geocoding)
	// below in _select2_init()

    });
    
    /* not jquery */

    // Not doing this because I don't know what jquery is doing to 'e'
    // such that e.detail throws an error (20140308/straup)
    //$("x-calendar").bind("datetap", function(e){

    var arrival_stage = document.getElementById("x-calendar-arrival-wrapper");
    var arrival_cal = arrival_stage.querySelector("x-calendar");

    arrival_cal.addEventListener("datetap", function(e){
	var date = e.detail.date;
	var dateStr = e.detail.iso;
	$("#arrival").val(dateStr);
    });

    var departure_stage = document.getElementById("x-calendar-departure-wrapper");
    var departure_cal = departure_stage.querySelector("x-calendar");

    departure_cal.addEventListener("datetap", function(e){
	var date = e.detail.date;
	var dateStr = e.detail.iso;
	$("#departure").val(dateStr);
    });

    /* we now return you to your regurlary scheduled jquery */

}

function privatesquare_trips_select2_init(){

    // http://ivaynberg.github.io/select2/

    // TO DO: sort out FQ URL for this (20140119/straup)	 
    var geocoder = privatesquare_abs_root_url() + "user_trips_add_geocode.php";
	 
    var s = $("#where").select2({
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

    s.on('select2-opening', function(){
	$("#x-calendar-arrival-wrapper").hide();
	$("#x-calendar-departure-wrapper").hide();
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

	$("#add-trip").attr("disabled", "disabled");

	privatesquare_api_call(method, args, _privatesquare_trips_add_trip_onsuccess);
	privatesquare_set_status("Adding trip to " + htmlspecialchars(name));

	return false;
    });
}

function _privatesquare_trips_add_trip_onsuccess(rsp){

    // console.log(rsp);

    if (rsp['stat'] != 'ok'){
	privatesquare_set_status(rsp['error']['error'], "danger");

	$("#add-trip").removeAttr("disabled");
	return false;
    }

    var url = rsp['trip']['trip_url'];
    location.href = url + "?success=1";
}

function privatesquare_trips_edit_init(){

    privatesquare_trips_datepicker_init();

    $("#trip-editor-show").click(function(){
    	$("#trip-summary").hide();
    	$("#trip-related").hide();
	$("#trip-editor").show();
	return false;
    });

    $("#trip-editor-cancel").click(function(){
	$("#trip-editor").hide();
    	$("#trip-summary").show();
    	$("#trip-related").show();
	return false;
    });

    $("#change-city").click(function(){

	var el = $(this);
	el.hide();

	privatesquare_trips_select2_init();

	$("#where").select2("open");
	return false;
    });

    $("#edit-trip").submit(function(){

	$("#where").select2("close");

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

    var trip = rsp['trip'];
    // console.log(trip);

    var short_name = $("#short-name");
    short_name.html(trip['locality']['woe_name']);
    short_name.attr("href", trip['place_url']);

    var long_name = $("#long-name");
    long_name.html(trip['locality']['name']);

    var status_str = $("#trip-status-str");

    if (status_str.html() != trip['status']){
	status_str.html(trip['status']);
    }
	
    $("#where").select2("destroy");
    $("#change-city").show();

    $("#trip-editor").hide();
    $("#trip-summary").show();
    $("#trip-related").show();

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
