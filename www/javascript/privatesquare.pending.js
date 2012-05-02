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
		privatesquare_fetch_venues(checkin['latitude'], checkin['longitude'], checkin['venue'], checkin['created']);

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
