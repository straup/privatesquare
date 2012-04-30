function privatesquare_deferred_checkin(lat, lon, reason){

	 privatesquare_unset_status();

	if (reason=='offline'){
		privatesquare_set_status("I've lost the network.");
	}

	else if (reason=='api error'){
		privatesquare_set_status("foursquare is sad.");
	}

	else if (reason){
		privatesquare_set_status(reason);
	}

	else {}

	_privatesquare_show_map(lat, lon);

	var w = $("#deferred_where");

	w.attr("data-latitude", lat);
	w.attr("data-longitude", lon);

	$("#deferred_checkin").submit(privatesquare_deferred_checkin_submit);
	$("#deferred").show();
}

function privatesquare_deferred_checkin_submit(){

	var dt = new Date();
	var ts = parseInt(dt.getTime() / 1000);

	var w = $("#deferred_where");

	var checkin = {
		'venue': w.val(),
		'latitude': w.attr('data-latitude'),
		'longitude': w.attr('data-longitude'),
		'created': ts
	};

	privatesquare_deferred_store(checkin, privatesquare_deferred_checkin_stored);
	return false;
}

function privatesquare_deferred_checkin_stored(checkin){

	var msg = "Okay! Your checkin at " + htmlspecialchars(checkin['venue']) + " has been recorded.";
	privatesquare_set_status(msg);

	_privatesquare_hide_map();
	$("#deferred_checkin").hide();
}

function privatesquare_deferred_indicator(){

	var store = new Store("privatesquare");
	var deferred = store.get("deferred");

	var count = (deferred) ? deferred.length : 0;

	var wrapper = $("#pending");
	var indicator = $("#pending_count");

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

function privatesquare_deferred_purge(){
	var store = new Store("privatesquare");
	store.remove("deferred");
}
