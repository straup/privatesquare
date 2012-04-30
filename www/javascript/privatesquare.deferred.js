function privatesquare_deferred_checkin(lat, lon, reason){

	 privatesquare_unset_status();

	if (reason === 'offline'){
		privatesquare_set_status("I've lost the network.");
	}

	if (reason === 'api error'){
		privatesquare_set_status("foursquare is sad.");
	}

	else if (reason){
		privatesquare_set_status(reason);
	}

	_privatesquare_show_map(lat, lon);

	var w = $("#deferred_where");
	w.attr("data-latitude", lat);
	w.attr("data-longitude", lon);

	$("#deferred_checkin").submit(privatesquare_deferred_checkin_submit);
	$("#deferred_checkin").show();
}

function privatesquare_deferred_checkin_submit(){

	var dt = new Date();
	var ts = parseInt(dt.getTime() / 1000);

	var w = $("#deferred_where");

	var checkin = {
		'location': w.val(),
		'latitude': w.attr('data-latitude'),
		'longitude': w.attr('data-longitude'),
		'created': ts
	};

	privatesquare_deferred_store(checkin, function(){ alert("STORED"); });
	return false;
}

function privatesquare_deferred_indicator(){

	var store = new Store("privatesquare");
	var deferred = store.get("deferred");

	var count = (deferred) ? deferred.length : 0;

	var wrapper = $("#deferred");
	var indicator = $("#deferred_count");

	if (count){
		indicator.html(count + " pending");
		wrapper.show();
	}

	else {
		indicator.html("");
		wrapper.hide();
	}
}

function privatesquare_deferred_store(checkin, callback){

	var store = new Store("privatesquare");
	var deferred = store.get("deferred");

	if (! deferred){
		deferred = new Array();
	}
		
	deferred.push(checkin);
	store.set("deferred", deferred);

	privatesquare_deferred_indicator();

	if (callback){
		callback(checkin);
	}
}
