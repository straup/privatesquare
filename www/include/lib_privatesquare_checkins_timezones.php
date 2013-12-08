<?php

	#################################################################

	function privatesquare_checkins_timezones_get_timezone(&$checkin){

		$venue_id = $checkin['venue_id'];
		$venue = venues_get_by_venue_id($venue_id);

		$map = venues_providers_map();

		if ($map[$venue['provider_id']] == 'foursquare'){

			$data = json_decode($venue['data'], 'as hash');
			dumper($data['timeZone']);
		}

		$lat = $checkin['latitude'];
		$lon = $checkin['longitude'];
	}

	#################################################################

	# the end
