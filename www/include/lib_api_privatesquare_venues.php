<?php

 	#################################################################

	function api_privatesquare_venues_checkin(){

		$provider_id = post_int32("provider_id");
		$provider = post_str("provider");

		$venue_id = post_str("venue_id");
		$status_id = post_int32("status_id");

		if (! $venue_id){
			api_output_error(999, "Missing venue ID");
		}

		if (! isset($status_id)){
			api_output_error(999, "Missing status ID");
		}

		if ($provider){
			$provider_id = venues_providers_label_to_id($provider);
		}

		if (! isset($provider_id)){
			api_output_error(999, "Missing provider ID");
		}

		if (! venues_providers_is_valid_provider($provider_id)){
			api_output_error(999, "Invalid provider ID");
		}

		$lat = post_float("latitude");
		$lon = post_float("longitude");

		if (($lat) && (! geo_utils_is_valid_latitude($lat))){
			api_output_error(999, "Invalid latitude");
		}

		if (($lon) && (! geo_utils_is_valid_longitude($lon))){
			api_output_error(999, "Invalid latitude");
		}

		$has_geo = (($lat) && ($lon)) ? 1 : 0;

		# where am I?

		$venue = venues_get_by_venue_id_for_provider($venue_id, $provider_id);

		if (! $venue){

			$rsp = venues_archive_venue_for_provider($venue_id, $provider_id);

			if ($rsp['ok']){
				$venue = $rsp['venue'];
			}
		}

		if (! $venue){
			api_output_error(999, "Failed to archive venue");
		}

		$checkin = array(
			'user_id' => $GLOBALS['cfg']['user']['id'],
			'status_id' => $status_id,
			'venue_id' => $venue['venue_id']
		);

		if ($created = post_int32("created")){
			$checkin['created'] = $created;
		}

		if ($has_geo){

			$checkin['latitude'] = $lat;
			$checkin['longitude'] = $lon;

			venues_geo_append_hierarchy($lat, $lon, $checkin);
		}

		else if ($venue){
			venues_geo_transfer_hierarchy($venue, $checkin);
		}

		else {}

		# Check to see if we're checking in to 4sq too

		# If this is a deferred checkin then we're just going to
		# ignore foursquare (for the time being) since there's no
		# way to back date checkins. One possibility is to make a
		# note of the backdating in the shout-out but in the interest
		# of keeping things simple to start, we're not going to.
		# (20120501/straup)

		if ($provider == 'foursquare'){

			if (($broadcast = post_str("broadcast")) && (! isset($checkin['created']))){

				$fsq_user = foursquare_users_get_by_user_id($GLOBALS['cfg']['user']['id']);
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
		}

		# If we already have a 'created' data that means this is a deferred
		# checkin being processed which means it's in the past which makes
		# it hard to ask for the weather. (20120501/straup)

		if (($GLOBALS['cfg']['enable_feature_weather_tracking']) && (! isset($checkin['created']))){

			loadlib("weather_yahoo");

			$rsp = weather_yahoo_conditions($checkin['latitude'], $checkin['longitude'], $checkin['locality']);

			if ($rsp['ok']){
				$conditions = $rsp['conditions'];
				$conditions['source'] = $rsp['source'];
				$checkin['weather'] = json_encode($conditions);
			}
		}

		# Actually store the checkin

		$rsp = privatesquare_checkins_create($checkin);

		if (! $rsp['ok']){
			api_output_error(999, "Check in failed");
		}

		$out = array(
			'checkin' => $rsp['checkin']
		);

		#

		$status_map = privatesquare_checkins_status_map('string keys');

		$send_to_littleprinter = 0;

		if ((features_is_enabled("bergcloud_users")) && (features_is_enabled("bergcloud_littleprinter"))){

			$send_to_littleprinter = 1;
		}

		if (($send_to_littleprinter) && ($status_id == $status_map['i want to go there'])){

			loadlib("bergcloud_users");
			loadlib("littleprinter");

			$berg_user = bergcloud_users_get_by_user_id($GLOBALS['cfg']['user']['id']);

			if (($berg_user) && ($berg_user['direct_print_code']) && ($berg_user['littleprinter_updates'])){
				$rsp = littleprinter_print_venue($venue, $berg_user);
			}
		}

		api_output_ok($out);
	}

 	#################################################################

	# the end
