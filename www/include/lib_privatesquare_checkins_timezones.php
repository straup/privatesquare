<?php

	loadlib("timezones");

	#################################################################

	function privatesquare_checkins_timezones_get_timezone(&$checkin){

		$venue_id = $checkin['venue_id'];
		$venue = venues_get_by_venue_id($venue_id);

		$map = venues_providers_map();

		if ($map[$venue['provider_id']] == 'foursquare'){

			$data = json_decode($venue['data'], 'as hash');

			if (($data) && ($tzid = $data['timeZone'])){
				$woeid = timezones_tzid_to_woeid($tzid);
				return $woeid;
			}
		}

		$lat = $checkin['latitude'];
		$lon = $checkin['longitude'];

		$rsp = timezones_get_for_latlon($lat, $lon);
		$rows = $rsp['rows'];

		return $rows[0]['woeid'];
	}

	#################################################################

	# the end
