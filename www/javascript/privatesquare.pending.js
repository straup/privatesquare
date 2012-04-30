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
	deferred.submit(privatesquare_pending_onselect);

	deferred.show();
}

function privatesquare_pending_onselect(){

	var checkins = $("#checkins");
	var where = checkins.val();

	if (where == '__purge__'){

		/* to do : count checkins */
		var q = 'Are you sure you want to delete all those pending checkins?';

		if (confirm(q)){

			var deferred = $("#deferred");
			var checkins = $("#checkins");

			checkins.html("");
			deferred.hide();

			privatesquare_deferred_purge();
			privatesquare_set_status("Okay all your pending checkins have been deleted.");
		}
	}

	else {

		console.log(where);
		var checkin = privatesquare_deferred_get_by_id(where);
		console.log(checkin);

		privatesquare_fetch_venues(checkin['latitude'], checkin['longitude'], checkin['venue']);
	}

	return false;
}
