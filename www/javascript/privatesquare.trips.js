function privatesquare_trips_add_init(){

    $("#dp-arrival").datepicker({'language':'en'});
    $("#dp-departure").datepicker({'language':'en'});

    // TO DO: on change event handler to update dp-departure
    // when dp-arrival changes (20140112/straup)

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

}
