<?php

	loadlib("privatesquare_checkins");

	loadlib("foursquare_users");
	loadlib("foursquare_venues");
	loadlib("foursquare_api");

 	#################################################################

	function api_privatesquare_venues_checkin(){

		$venue_id = post_str("venue_id");
		$status_id = post_int32("status_id");

		if (! $venue_id){
			api_output_error(999, "Missing venue ID");
		}

		if (! isset($status_id)){
			api_output_error(999, "Missing status ID");
		}

		$fsq_user = foursquare_users_get_by_user_id($GLOBALS['cfg']['user']['id']);

		$checkin = array(
			'user_id' => $GLOBALS['cfg']['user']['id'],
			'venue_id' => $venue_id,
			'status_id' => $status_id,
		);

		# where am I?

		$venue = foursquare_venues_get_by_venue_id($venue_id);

		if (! $venue){
			$rsp = foursquare_venues_archive_venue($venue_id);

			if ($rsp['ok']){
				$venue = $rsp['venue'];
			}
		}

		if ($venue){
			$checkin['locality'] = $venue['locality'];
			$checkin['latitude'] = $venue['latitude'];
			$checkin['longitude'] = $venue['longitude'];
		}

		# check to see if we're checking in to 4sq too

		if ($broadcast = post_str("broadcast")){

			$method = 'checkins/add';

			$args = array(
				'oauth_token' => $fsq_user['oauth_token'],
				'venueId' => $venue_id,
				'broadcast' => $broadcast,
			);

			$more = array(
				'method' => 'POST',
			);

			$rsp = foursquare_api_call($method, $args, $more);

			if ($rsp['ok']){
				$checkin['checkin_id'] = $rsp['rsp']['checkin']['id'];
			}

			# on error, then what?
		}

		if ($GLOBALS['cfg']['enable_feature_weather_tracking']){

			loadlib("weather_google");

			$rsp = weather_google_conditions($checkin['latitude'], $checkin['longitude']);

			if ($rsp['ok']){
				$conditions = $rsp['conditions'];
				$conditions['source'] = $rsp['source'];
				$checkin['weather'] = json_encode($conditions);
			}
		}

		$rsp = privatesquare_checkins_create($checkin);

		if (! $rsp['ok']){
			api_output_error(999, "Check in failed");
		}

		$out = array(
			'checkin' => $rsp['checkin']
		);

		api_output_ok($out);
	}

 	#################################################################

?>
