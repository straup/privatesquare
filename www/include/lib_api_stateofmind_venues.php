<?php

 	#################################################################

	function api_stateofmind_venues_search(){

		$lat = request_float('latitude');
		$lon = request_float('longitude');

		if (($lat) && (! geo_utils_is_valid_latitude($lat))){
			api_output_error(999, "Missing or invalid latitude");
		}

		if (($lon) && (! geo_utils_is_valid_longitude($lon))){
			api_output_error(999, "Missing or invalid longitude");
		}

		# TO DO eventually: check to see if we're on land
		# or water or whatever (20131104/straup)

		$venues = venues_stateofmind_venues();

		$checkin_crumb = crumb_generate("api", "privatesquare.venues.checkin");
		$fsq_user = foursquare_users_get_by_user_id($GLOBALS['cfg']['user']['id']);

		$out = array(
			'venues' => $venues,
			'latitude' => $lat,
			'longitude' => $lon,
			'crumb' => $checkin_crumb,
		);

		api_output_ok($out);

	}

 	#################################################################

	# the end
