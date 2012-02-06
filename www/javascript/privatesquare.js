var checking_in=false;
var searching=false;

function privatesquare_init(){
	navigator.geolocation.getCurrentPosition(_privatesquare_geolocation_onsuccess, _privatesquare_geolocation_onerror);
	$("#checkin").submit(privatesquare_submit);
	$("#again").click(privatesquare_reset);
	$("#status").html("Asking the sky where you are...");
}

function privatesquare_reset(){
	$("#venues").hide();
	$("#status").html();

	_privatesquare_hide_map()
	privatesquare_init();
}

function privatesquare_submit(){

	// this shouldn't be necessary, but is for now...

	if (checking_in){
		return false;
	}

	var venue_id = $("#where").val();
	var status_id = $("#what").val();
	var broadcast = $("#broadcast").val();

	// not thrilled about this...
	broadcast = (status_id==2) ? "" : broadcast;

	if (venue_id == -1){
		privatesquare_search();
		return false;
	}

	checking_in=true;

	var crumb = $("#where").attr("data-crumb");

	var args = {
		'method': 'privatesquare.venues.checkin',
		'crumb': crumb,
		'venue_id': venue_id,
		'status_id': status_id,
		'broadcast': broadcast
	};

	$.ajax({
		'url': _cfg.abs_root_url + 'api/',
		'type': 'POST',
		'data': args,
		'success': _privatesquare_checkin_onsuccess
	});

	$("#venues").hide();

	_privatesquare_hide_map()

	$("#status").html("Checking in...");
	return false;
}

function _privatesquare_geolocation_onsuccess(rsp){
	var lat = rsp['coords']['latitude'];
	var lon = rsp['coords']['longitude'];
	privatesquare_fetch_venues(lat, lon);
}

function privatesquare_search(){

	if (searching){
		return false;
	}

	searching = true;

	_privatesquare_hide_map();
	$("#venues").hide();

	var query = prompt("Search for a particular place");

	if (! query){
		var msg = 'Okay, I\'m giving up. <a href="#" onclick="privatesquare_init();return false;">Start over</a> if you want change your mind.';
		$("#status").html(msg);
		return false;
	}

	var _onsuccess = function(rsp){
		var lat = rsp['coords']['latitude'];
		var lon = rsp['coords']['longitude'];
		privatesquare_fetch_venues(lat, lon, query);
		searching = false;
		return;
	};

	var _onerror = function(rsp){};

	navigator.geolocation.getCurrentPosition(_onsuccess, _onerror);

	$("#status").html("Re-checking your location first...");
	return false;
}

function privatesquare_fetch_venues(lat, lon, query){

	var args = {
		'method': 'foursquare.venues.search',
		'latitude': lat,
		'longitude': lon
	};

	if (query != ''){
		args['query'] = query;
	}

	$.ajax({
		'url': _cfg.abs_root_url + 'api/',
		'data': args,
		'success': _foursquare_venues_onsuccess
	});
 
	$("#status").html("Fetching nearby places...");
}

function _privatesquare_geolocation_onerror(rsp){
	$("#status").html("Huh. I have no idea where you are...");
}

function _foursquare_venues_onsuccess(rsp){

	// see above
	checking_in=false;

	$("#status").html("");

	if (rsp['stat'] != 'ok'){
		_privatesquare_api_error(rsp);
		return;
	}

	var count = rsp['venues'].length;

	if (! count){
		var msg = 'You appear to have fallen in to a black hole. There\'s nothing around here';

		if (rsp['query']){
			msg += ' that looks like <q>' + htmlspecialchars(rsp['query']) + '</q>';
		}

		msg += '. <a href="#" onclick="privatesquare_search();return false;">Try again</a>';
		msg += ' or <a href="#" onclick="privatesquare_init();return false;">start from scratch</a>?';
		$("#status").html(msg);
		return;
	}

	var html = '';

	for (var i=0; i < count; i++){
		var v = rsp['venues'][i];
		html += '<option value="' + v['id'] + '">' + v['name'] + '</option>';
	}

	html += '<option value="-1">–– none of the above / search ––</option>';

	var where = $("#where");
	where.attr("data-crumb", rsp['crumb']);
	where.html(html);
	where.change(_privatesquare_where_onchange);

	$("#what").change(_privatesquare_what_onchange);

	// draw the map...

	_privatesquare_show_map(rsp['latitude'], rsp['longitude']);

	$("#status").html("");
	$("#venues").show();
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

	if (what == 2){
		broadcast.attr("disabled", "disabled");
	}
	
	else {
		broadcast.removeAttr("disabled");
	}
}

function _privatesquare_checkin_onsuccess(rsp){

	$("#status").html("");

	if (rsp['stat'] != 'ok'){
		_privatesquare_api_error(rsp);
		return;
	}

	var msg = "Success!";
	msg += ' <a href="#" onclick="privatesquare_init();return false;">Do it again?</a>';

	$("#status").html(msg);
}

function _privatesquare_api_error(rsp, action){

	var msg = 'Oh noes. There was a problem completing your request. ';

	var err = rsp['error'];

	if (err){
		msg += 'The robot monkeys report: ';
		msg += err['error'] + ' (error code #' + err['code'] + '). ';
	}

	else {
		msg += 'It appears to be a privatesquare problem rather than foursquare weirdness. ';
	}

	msg += '<a href="#" onclick="privatesquare_init();return false;">Try it again?</a>';

	$("#status").html(msg);
}

function _privatesquare_show_map(lat, lon){

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
	mrk.html("you are here-ish");

	map.html(mrk);
	wrapper.html(map)

	wrapper.show();
	map.htmapl();
}

function _privatesquare_hide_map(){
	var wrapper = $("#map-wrapper");
	var map = $(".map");
	wrapper.hide();
	map.remove();
}
