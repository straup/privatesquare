var checking_in=false;
var searching=false;

function privatesquare_init(){
	navigator.geolocation.getCurrentPosition(_privatesquare_geolocation_onsuccess, _privatesquare_geolocation_onerror);
	$("#checkin").submit(privatesquare_submit);
	$("#again").click(privatesquare_reset);

	privatesquare_set_status("Asking the sky where you are...");
}

function privatesquare_reset(){
	$("#venues").hide();

	privatesquare_unset_status();
	_privatesquare_hide_map()
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

	_privatesquare_hide_map()

	privatesquare_set_status("Checking in...");
	return false;
}

function privatesquare_gather_args(){

	var venue_id = $("#where").val();
	var status_id = $("#what").val();
	var broadcast = $("#broadcast").val();

	// not thrilled about this...
	broadcast = (status_id==2) ? "" : broadcast;

	var crumb = $("#where").attr("data-crumb");

	return {
		'crumb': crumb,
		'venue_id': venue_id,
		'status_id': status_id,
		'broadcast': broadcast
	};
}

function privatesquare_checkin(args, onsuccess){

	if (checking_in){
		return false;
	}

	if (! onsuccess){
		onsuccess = privatesquare_checkin_onsuccess;
	}

	checking_in=true;

	args['method'] = 'privatesquare.venues.checkin';

	$.ajax({
		'url': _cfg.abs_root_url + 'api/',
		'type': 'POST',
		'data': args,
		'error': function(rsp){
			checking_in=false;
			/* console.log("ERROR"); */
		},
		'success': function(rsp){
			checking_in=false;
			onsuccess(rsp);
		}
	});

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
		privatesquare_set_status(msg);
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

	privatesquare_set_status("Re-checking your location first...");
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
 
	privatesquare_set_status("Fetching nearby places...");
}

function _privatesquare_geolocation_onerror(rsp){
	privatesquare_set_status("Huh. I have no idea where you are...");
}

function _foursquare_venues_onsuccess(rsp){

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

		privatesquare_set_status(msg);
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

	privatesquare_unset_status();
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

function privatesquare_checkin_onsuccess(rsp, tryagain_func){

	$("#status").html("");

	if (! tryagain_func){
		tryagain_func = privatesquare_init;
	}

	if (rsp['stat'] != 'ok'){
		privatesquare_api_error(rsp, tryagain_func);
		return;
	}

	var loc = _cfg['abs_root_url' ] + 'venue/' + rsp['checkin']['venue_id'] + '?success=1';
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
		msg += '<button id="tryagain">Try it again?</button>';
		msg += '&#160;&#160;';
		msg += '<button id="donot_tryagain">Forget it</button>';
	}

	privatesquare_set_status(msg);

	if (tryagain_func){
		$("#tryagain").click(tryagain_func);

		$("#donot_tryagain").click(function(){
			$("#status").html("");
			$("#status").hide();
		});
	}

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

function privatesquare_set_status(msg){
	$("#status").html(msg);
	$("#status").show();
}

function privatesquare_unset_status(){
	$("#status").html("");
	$("#status").hide();
}
