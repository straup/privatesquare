function privatesquare_pending_init(){

	var pending = privatesquare_deferred_list();

	if (pending.length == 0){
		privatesquare_set_status("There are no pending checkins to administriviate.");
		return;
	}

	var prettydate = function(dt){

		var mmToMonth = new Array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

		var yyyy = dt.getFullYear();
		var dd = dt.getDate();
		var mm = mmToMonth[dt.getMonth()];

		/* include time? */
		return mm + " " + dd;
	}

	var html = '<optgroup>';

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

	var msg = "There are " + pending.length + " pending checkins."

	if (pending.length == 1){
		msg = "You have one pending checkin.";
	}

	privatesquare_set_status(msg);
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
			_privatesquare_pending_fetch_venues_onload(rsp);
		}
	});
 
	privatesquare_set_status("Looking for '" + htmlspecialchars(checkin['venue']) + "'");
}

function _privatesquare_pending_fetch_venues_onload(rsp){

	privatesquare_unset_status();

	if (rsp['stat'] != 'ok'){
		privatesquare_api_error(rsp);
		return;
	}

	var count = rsp['venues'].length;

	if (! count){
		privatesquare_set_status("Ack! foursquare can't find like '" + htmlspecialchars(checkin['venue']) + "' around there...");
		return;
	}

	var html = '';
	html += '<optgroup>';

	for (var i=0; i < count; i++){
		var v = rsp['venues'][i];
		html += '<option value="' + v['id'] + '">' + v['name'] + '</option>';
	}

	html += '</optgroup>';

	html += '<optgroup class="admin">';
	html += '<option value="-1">Meh, just delete this...</option>';
	html += '</optgroup>';

	var where = $("#where");
	where.attr("data-crumb", rsp['crumb']);
	where.attr("data-checkin-id", rsp['checkin']['id']);

	where.html(html);

	$("#what").attr("disabled", "disabled");
	$("#broadcast").attr("disabled", "disabled");

	_privatesquare_show_map(rsp['latitude'], rsp['longitude'], '"' + rsp['checkin']['venue'] + '"');

	$("#venues").show();

	$("#checkin").submit(_privatesquare_pending_onsubmit);

}

function _privatesquare_pending_onsubmit(){

	$("#venues").hide();

	_privatesquare_hide_map()

	var args = privatesquare_gather_args();

	var where = $("#where");
	var id = where.attr("data-checkin-id");
	var checkin = privatesquare_deferred_get_by_id(id);

	args['created'] = checkin['created'];

	if (args['venue_id'] == -1){

		var q = "Are you sure you want to delete this pending checkin?";

		if (confirm(q)){

			privatesquare_deferred_remove(checkin['id']);

			var pending = privatesquare_deferred_list();

			if (pending.length == 0){
				privatesquare_set_status("All your pending checkins have been encheckin-ified (or deleted).");
			}

			else {
				privatesquare_set_status("Your checkin at '" + htmlspecialchars(checkin['venue']) + "' has deleted.");
				privatesquare_pending_init();
			}
		}

		return false;
	}

	privatesquare_checkin(args, function(rsp){
		rsp['checkin'] = checkin;
		_privatesquare_pending_checkin_onload(rsp);
	});

	privatesquare_set_status("Checking in...");
	return false;
}

function _privatesquare_pending_checkin_onload(rsp){

	if (rsp['stat'] != 'ok'){
		privatesquare_api_error(rsp);
		return;
	}

	privatesquare_deferred_remove(rsp['checkin']['id']);

	var pending = privatesquare_deferred_list();

	if (pending.length == 0){
		privatesquare_set_status("All your pending checkins have been encheckin-ified!");
		return;
	}

	privatesquare_set_status("Your checkin at '" + htmlspecialchars(rsp['checkin']['venue']) + "' has been encheckin-ified!");
	privatesquare_pending_init();
}
