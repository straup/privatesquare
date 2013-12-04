var checking_in=false;
var searching=false;

function privatesquare_init(provider){

	if (! provider){
	    provider = 'foursquare';
	}

	$("body").attr("data-privatesquare-provider", provider);

	if (! window.navigator.onLine){
		privatesquare_deferred_checkin(null, null, 'offline');
		return;
	}

	var _privatesquare_geolocation_onsuccess = function(rsp){

		var lat = rsp['coords']['latitude'];
		var lon = rsp['coords']['longitude'];

		var v = $("#venues");
		v.attr("data-geolocation-latitude", lat);
		v.attr("data-geolocation-longitude", lon);

		if (provider=='stateofmind'){
			privatesquare_stateofmind_fetch_venues(lat, lon);
		}

		else if (provider=='nypl'){
			privatesquare_nypl_fetch_venues(lat, lon);
		}

		else if (provider=='privatesquare'){
			privatesquare_venues_fetch_venues(lat, lon);
		}

		else {
			privatesquare_foursquare_fetch_venues(lat, lon);
		}
	};

	var _privatesquare_geolocation_onerror = function(rsp){
		privatesquare_set_status("Huh. I have no idea where you are...");
	};

	privatesquare_whereami(_privatesquare_geolocation_onsuccess, _privatesquare_geolocation_onerror);
	$("#checkin").submit(privatesquare_submit);
	$("#again").click(privatesquare_reset);

	privatesquare_set_status("Asking the sky where you are...");
}

// to do : capture current source...

function privatesquare_reset(){
	$("#venues").hide();

	privatesquare_unset_status();
	privatesquare_hide_map()
	privatesquare_init();
}

function privatesquare_submit(){

	var args = privatesquare_gather_args();

	if (args['venue_id'] == -1){
		privatesquare_search();
		return false;
	}

	privatesquare_checkin(args, privatesquare_checkin_onsuccess);

	$("#venues").hide();

	privatesquare_hide_map()

	privatesquare_set_status("Checking in...");
	return false;
}

function privatesquare_gather_args(){

	var v = $("#venues");

	var provider = v.attr("data-venues-provider");
	var lat = v.attr("data-geolocation-latitude");
	var lon = v.attr("data-geolocation-longitude");

	var venue_id = $("#where").val();
	var status_id = $("#what").val();
	var broadcast = $("#broadcast").val();

	if (! provider){
		provider = $("body").attr("data-privatesquare-provider");
	}

	// not thrilled about this...
	broadcast = (status_id==2) ? "" : broadcast;

	var crumb = $("#where").attr("data-crumb");

	var args = {
		'crumb': crumb,
		'venue_id': venue_id,
	        'provider': provider,
		'status_id': status_id,
		'broadcast': broadcast
	};

	if ((lat) && (lon)){
	    args['latitude'] = lat;
	    args['longitude'] = lon;
	}

	return args;
}

function privatesquare_checkin(args, on_success){

	if (checking_in){
		return false;
	}

	if (! on_success){
		on_success = privatesquare_checkin_onsuccess;
	}

	var on_error = function(rsp){
		checking_in = false;
	};
    
	checking_in=true;

	var method = 'privatesquare.venues.checkin';

	privatesquare_api_call(method, args, on_success, on_error);
}

// currently assumed to be foursquare since nothing else has search
// this is reflected in the code below (20131104/straup)

function privatesquare_search(){

	if (searching){
		return false;
	}

	searching = true;

	privatesquare_hide_map();
	$("#venues").hide();

	var query = prompt("Search for a particular place");

	if (! query){

		var v = $("#venues");
		var provider = v.attr("data-venues-provider");

		var msg = 'Okay, I\'m giving up. <a href="#" onclick="privatesquare_init();return false;">Start over</a> if you want change your mind.';
		privatesquare_set_status(msg);
		return false;
	}

	var provider = privatesquare_venues_provider();
	var on_success = null;

	console.log('provider is ' + provider);

	if (provider == 'foursquare'){

		on_success = function(rsp){
			var lat = rsp['coords']['latitude'];
			var lon = rsp['coords']['longitude'];

			privatesquare_foursquare_fetch_venues(lat, lon, query);
			searching = false;
			return;
		};
	}

	else if (provider == 'nypl'){

		on_success = function(rsp){
			var lat = rsp['coords']['latitude'];
			var lon = rsp['coords']['longitude'];

			privatesquare_nypl_fetch_venues(lat, lon, query);
			searching = false;
			return;
		};
	}

	else {

		privatesquare_set_status("Hrm... I don't know how to search for that", "warning");
		return false;
	}

	var on_error = function(rsp){};

	privatesquare_whereami(on_success, on_error);

	privatesquare_set_status("Re-checking your location first...");
	return false;
}

function _privatesquare_where_onchange(){

	var venue_id = $("#where").val();

	if (venue_id == -1){
		privatesquare_search();
	}

	return false;
}

function _privatesquare_what_onchange(){

	var what = $("#what").val();
	var broadcast = $("#broadcast");

	// put me in a function?
	var provider = $("body").attr("data-privatesquare-provider");

	if (what == 2){
		broadcast.attr("disabled", "disabled");
	}

	// hrm... this is not ideal (20131117/straup)

	else if (provider == 'stateofmind'){
		// pass
	}

	else {
		broadcast.removeAttr("disabled");
	}
}

function privatesquare_checkin_onsuccess(rsp, tryagain_func){

	$("#status").html("");

	if (! tryagain_func){
		tryagain_func = privatesquare_init;
	}

	if (rsp['stat'] != 'ok'){
		privatesquare_api_error(rsp, tryagain_func);
		return;
	}

	var loc = privatesquare_abs_root_url() + 'venue/' + rsp['checkin']['venue_id'] + '?success=1';
	location.href = loc;
}

function privatesquare_api_error(rsp, tryagain_func){

	var msg = 'Oh noes. There was a problem completing your request. ';

	var err = rsp['error'];

	if (err){
		msg += 'The robot monkeys report: ';
		msg += err['error'] + ' (error code #' + err['code'] + '). ';
	}

	else {
		msg += 'It appears to be a privatesquare problem rather than foursquare weirdness. ';
	}

	if (tryagain_func){
		msg += '<button id="tryagain" class="btn">Try it again?</button>';
		msg += '&#160;&#160;';
		msg += '<button id="donot_tryagain" class="btn">Forget it</button>';
	}

	privatesquare_set_status(msg, "warning");

	if (tryagain_func){
		$("#tryagain").click(tryagain_func);

		$("#donot_tryagain").click(function(){
			$("#status").html("");
			$("#status").hide();
		});
	}

}

function privatesquare_show_map(lat, lon, label){

	if (! label){
		label = "you are here-ish";
	}

	var latlon = lat + ',' + lon;

	var wrapper = $("#map-wrapper");

	var map = document.createElement("div");
	map = $(map);

	// just pass these are args to the htmapl function call?

	map.attr("class", "map");
	map.attr("data-zoom", 14);
	map.attr("data-center", latlon);
	map.attr("data-hash", false);
	map.attr("data-touch", true);
	map.attr("data-provider", "toner");

	var mrk = document.createElement("div");
	mrk = $(mrk);

	mrk.attr("class", "marker")
	mrk.attr("data-location", latlon)
	mrk.html(htmlspecialchars(label));

	map.html(mrk);
	wrapper.html(map)

	wrapper.show();

	privatesquare_htmapl(map);
}

function privatesquare_show_map_bbox(bbox,venues){

	var wrapper = $("#map-wrapper");

	var map = document.createElement("div");
	map = $(map);

	map.attr("class", "map");
	map.attr("data-extent", bbox.join(','));
	map.attr("data-hash", false);
	map.attr("data-touch", true);
	map.attr("data-provider", "toner");

	var markers = [];

	var count_venues = venues.length;

	for (var i=0; i < count_venues; i++){

		var venue = venues[i];
		var lat = venue[0];
		var lon = venue[1];
		var label = venue[2];

		var latlon = lat + ',' + lon;

		var mrk = document.createElement("div");
		mrk = $(mrk);

		mrk.attr("class", "marker marker-header")
		mrk.attr("data-location", latlon);
		// mrk.html(htmlspecialchars(label));

		// http://stackoverflow.com/questions/2419749/get-selected-elements-outer-html
		// sigh... (20121220/straup)

		markers.push(mrk.clone().wrap('<div/>').parent().html());
	}

	if (markers.length){
		map.html(markers.join(''));
	}

	wrapper.html(map)
	wrapper.show();

	privatesquare_htmapl(map);
}

function privatesquare_htmapl(map){

	if (! map){
		map = $(".map");
	}

	try {
		map.htmapl();
	}

	catch (e){
		map.html('<div class="map-error alert alert-warning">hrmph...failed to load map: ' + e + '</div>');
	}
}

function privatesquare_hide_map(){
	var wrapper = $("#map-wrapper");
	var map = $(".map");
	wrapper.hide();
	map.remove();
}

function privatesquare_set_status(msg, alert_type){

	if (! alert_type){
	    alert_type = "info";
	}

	var status = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
	status += msg;

	$("#status").attr("class", "alert alert-dismissable alert-" + alert_type);
	$("#status").html(status);
	$("#status").show();
}

function privatesquare_unset_status(){
	$("#status").html("");
	$("#status").hide();
}

function privatesquare_whereami(onsuccess, onerror){

	/* this shouldn't be necessary but it also seems to be
	   where the weirdness with /nearby is happening...
	   (20120604/straup) */

	try {
		var args = { enableHighAccuracy:true, maximumAge: 1000 };
		navigator.geolocation.getCurrentPosition(onsuccess, onerror, args);
	}

	catch (e) {
	      alert("The sky is angry offering only this, today: " + e);
	}

}

function privatesquare_abs_root_url(){
	return document.body.getAttribute("data-abs-root-url");
}

function privatesquare_venues_provider(){
	 return $("body").attr("data-privatesquare-provider");
}
