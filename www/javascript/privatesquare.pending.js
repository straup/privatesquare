function privatesquare_pending_init(){

	var pending = privatesquare_deferred_list();

	if (pending.length == 0){
		privatesquare_set_status("There are no pending checkins to administriviate.");
		return;
	}

	var html = '<optgroup>';

	var prettydate = function(dt){
		var mmToMonth = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

		var yyyy = dt.getFullYear();
		var dd = dt.getDate();
		var mm = mmToMonth[dt.getMonth()];

		return mm + " " + dd;
	}

	for (var i in pending){

		var checkin = pending[i];
		var dt = new Date(checkin['created'] * 1000);


		html += '<option value="' + htmlspecialchars(checkin['id']) + '">';
		html += prettydate(dt) + ' at "';
		html += htmlspecialchars(checkin['venue']);
		html += '"</option>';
	}

	html += '</optgroup>';
	html += '<optgroup class="admin">';

	html += '<option value="__purge__">';
	html += (pending.length > 1) ? 'Delete all pending checkins' : 'Just forget this checkin';
	html += '</option>';

	html += '</optgroup>';

	var checkins = $("#checkins");
	checkins.html(html);

	var deferred = $("#deferred");
	deferred.attr('data-count-pending', pending.length);
	deferred.submit(privatesquare_pending_onselect);

	deferred.show();
}

function privatesquare_pending_onselect(){

	var deferred = $("#deferred");
	var checkins = $("#checkins");

	var where = checkins.val();

	if (where == '__purge__'){

		var q = 'Are you sure you want to delete all those pending checkins?';

		var count_pending = deferred.attr('data-count-pending');

		if (count_pending == 1){
			q = 'Are you sure you want to delete this checkin?';
		}

		if (confirm(q)){

			checkins.html("");
			deferred.hide();

			privatesquare_deferred_purge();
			privatesquare_set_status("Okay all your pending checkins have been deleted.");
		}
	}

	else {

		var checkin = privatesquare_deferred_get_by_id(where);
		privatesquare_pending_fetch_venues(checkin);

		deferred.hide();

		/*
		 to do: enforce 'I was here' (don't tell foursquare?)
		 to do: assign create data...
		 to do: hide 'to it again'
		 to do: do not let user create deferred checkin if api call fails
		*/
	}

	return false;
}

function privatesquare_pending_fetch_venues(checkin){

	var args = {
		'method': 'foursquare.venues.search',
		'latitude': checkin['latitude'],
		'longitude': checkin['longitude'],
		'query': checkin['venue']
	};

	$.ajax({
		'url': _cfg.abs_root_url + 'api/',
		'data': args,
		'success': function(rsp){
			rsp['checkin'] = checkin;
			_foo(rsp);
		}
	});
 
	privatesquare_set_status("Fetching nearby places...");
}

function _foo(rsp){

	privatesquare_unset_status();

	if (rsp['stat'] != 'ok'){
		privatesquare_set_status("FAIL:api");
		return;
	}

	var count = rsp['venues'].length;

	if (! count){
		privatesquare_set_status("FAIL: no venues");
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
	where.attr("data-checkin-id", rsp['checkin']['id']);

	where.html(html);
	where.change(_privatesquare_where_onchange);

	$("#what").change(_privatesquare_what_onchange);

	// draw the map...

	_privatesquare_show_map(rsp['latitude'], rsp['longitude']);

	privatesquare_unset_status();
	$("#venues").show();

	$("#checkin").submit(function(){
try{
_privatesquare_pending_onsubmit();
} catch (e){
console.log(e);
}

return false;
});
}

/* need to figure out how to pass checking (id) around... */

function _privatesquare_pending_onsubmit(){

	var args = privatesquare_gather_args();

	if (args['venue_id'] == -1){
		privatesquare_search();
		return false;
	}

	var where = $("#where");
	var id = where.attr("data-checkin-id");
	var checkin = privatesquare_deferred_get_by_id(id);

	args['created'] = checkin['created'];

	privatesquare_checkin(args, function(rsp){
		rsp['checkin'] = checkin;
		_privatesquare_pending_checkin_onsuccess(rsp);
	});

	$("#venues").hide();

	_privatesquare_hide_map()

	privatesquare_set_status("Checking in...");
	return false;
}

function _privatesquare_pending_checkin_onsuccess(rsp){
	console.log(rsp);
	privatesquare_deferred_remove(rsp['checkin']['id']);
}
