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

	var woeid = $("#where").val();
	var arr = $("#arrival").val();
	var dept = $("#departure").val();
	var notes = $("#notes").val();

	var method = 'privatesquare.trips.add';

	var args = {
	    'woeid': woeid,
	    'arrival': arr,
	    'departure': dept,
	    'notes': notes,
	};

	console.log(args);

	return false;
    });
}
