function privatesquare_stateofmind_fetch_venues(lat, lon){

	// Really? Yeah, I think so. We'll see...
	// (20131117/straup)

	$("#what option").each(function(){
		var opt = $(this);

		if (opt.val() > 1){
			opt.attr("disabled", "disabled");
		}
	});

	$("#broadcast").attr("disabled", "disabled");

	var method = 'stateofmind.venues.search';

	var args = {
		'latitude': lat,
		'longitude': lon
	};

	privatesquare_api_call(method, args, _privatesquare_stateofmind_fetch_venues_onsuccess);
	privatesquare_set_status("Fetching nearby places...");
}

function _privatesquare_stateofmind_fetch_venues_onsuccess(rsp){

	privatesquare_unset_status();

	if (rsp['stat'] != 'ok'){

		var _okay = function(rsp){
			var lat = rsp['coords']['latitude'];
			var lon = rsp['coords']['longitude'];
			privatesquare_deferred_checkin(lat, lon, 'api error');
		};

		var _not_okay = function(){
			privatesquare_api_error(rsp);
		}

		privatesquare_whereami(_okay, _not_okay);
		return;
	}

	var venues = $("#venues");
	venues.attr("data-venues-provider", "stateofmind");

    	var count = rsp['venues'].length;

	var html = '';

	for (var i=0; i < count; i++){
		var v = rsp['venues'][i];
		html += '<option value="' + v['id'] + '">' + v['name'] + '</option>';
	}

	// html += '<option value="-1">–– none of the above / search ––</option>';

	var where = $("#where");
	where.attr("data-crumb", rsp['crumb']);
	where.html(html);
	where.change(_privatesquare_where_onchange);

	$("#what").change(_privatesquare_what_onchange);

	// draw the map...

	privatesquare_show_map(rsp['latitude'], rsp['longitude']);

	privatesquare_unset_status();
	$("#venues").show();

	$("#checkin").submit(privatesquare_submit);
}
