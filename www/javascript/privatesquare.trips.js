function privatesquare_trips_add_init(){

    $("#arrival").datepicker({'language':'en'});
    $("#departure").datepicker({'language':'en'});

    // TO DO: on change event handler to update departure
    // when arrival changes (20140112/straup)

    // TO DO: on change event handler to ensure departure
    // is not before arrival (20140112/straup)

    $("#where").select2({
        minimumInputLength: 3,
	ajax: {
	    url: "http://privatesquare/user_trips_add_geocode.php",
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

    $("#add-trip").submit(function(){

	var form = $("#add-trip");
	var crumb = form.attr("data-add-trip-crumb");

	var woeid = $("#where").val();
	var arr = $("#arrival").val();
	var dept = $("#departure").val();

	var arr_by = $("#arrive_by").val();
	var dept_by = $("#depart_by").val();

	var note = $("#note").val();

	var method = 'privatesquare.trips.add';

	var args = {
	    'woeid': woeid,
	    'arrival': arr,
	    'departure': dept,
	    'arrive_by': arr_by,
	    'depart_by': dept_by,
	    'note': note,
	    'crumb': crumb
	};

	console.log(args);

	try {
	privatesquare_api_call(method, args, _privatesquare_trips_add_trip_onsuccess);
	privatesquare_set_status("Adding trip to [FIX ME]");
	    } catch(e){
		console.log(e);
	    }

	return false;
    });
}

function _privatesquare_trips_add_trip_onsuccess(rsp){
	console.log(rsp);
}
