<?php

	loadlib("trips");

 	#################################################################

	function api_privatesquare_trips_add(){

		$woeid = post_int32("woeid");

		$date_arrival = post_str("arrival");
		$date_departure = post_str("departure");

		$trip = array(
			'user_id' => $GLOBALS['cfg']['user']['id'],
		);

		$rsp = trips_add_trip($trip);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		$out = array(
			'trip' => $rsp['trip'],
		);

		api_output_ok($out);
	}

 	#################################################################

	# the end
