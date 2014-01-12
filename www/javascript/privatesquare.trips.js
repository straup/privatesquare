function privatesquare_trips_add_init(){

    $("#dp-arrival").datepicker({'language':'en'});
    $("#dp-departure").datepicker({'language':'en'});

    // TO DO: on change event handler to update dp-departure
    // when dp-arrival changes (20140112/straup)

    // TO DO: on change event handler to ensure dp-departure
    // is not before dp-arrival (20140112/straup)

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
	var arr = $("#dp-arrival").val();
	var dept = $("#dp-departure").val();

	var method = 'privatesquare.trips.add';

	var args = {
	    'woeid': woeid,
	    'arrival': arr,
	    'departure': dept
	};

	console.log(args);

	return false;
    });
}
