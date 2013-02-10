<?php

	#################################################################

	function foursquare_venues_get_by_venue_id($id){

		$enc_id = AddSlashes($id);
		$sql = "SELECT * FROM FoursquareVenues WHERE venue_id='{$enc_id}'";

		return db_single(db_fetch($sql));
	}

	#################################################################

	function foursquare_venues_fetch_venue($venue_id){

		loadlib("foursquare_users");
		loadlib("foursquare_api");

		$fsq_user = foursquare_users_random_user();

		$method = "venues/{$venue_id}";

		$args = array(
			'oauth_token' => $fsq_user['oauth_token'],
		);

		$rsp = foursquare_api_call($method, $args);
		return $rsp;
	}

	#################################################################

	function foursquare_venues_archive_venue($venue_id){

		loadlib("reverse_geoplanet");

		$rsp = foursquare_venues_fetch_venue($venue_id);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = $rsp['rsp']['venue'];

		$lat = $data['location']['lat'];
		$lon = $data['location']['lng'];

		$venue = array(
			'venue_id' => $data['id'],
			'name' => $data['name'],
			'latitude' => $lat,
			'longitude' => $lon,
			'data' => json_encode($data),
		);

		# might be better/easier to geocode string place names (20120121/straup)

		$geo_rsp = reverse_geoplanet($lat, $lon, $GLOBALS['cfg']['reverse_geoplanet_remote_endpoint']);

		if ($geo_rsp['ok']){
			$venue['locality'] = $geo_rsp['data']['locality'];
		}

		return foursquare_venues_add_venue($venue);
	}

	#################################################################

	function foursquare_venues_add_venue($venue){

		$insert = array();

		foreach ($venue as $k => $v){
			$insert[$k] = AddSlashes($v);
		}	

		$rsp = db_insert('FoursquareVenues', $insert);

		if (($rsp['ok']) || ($rsp['error_code'] == 1062)){
			$rsp['venue'] = $venue;
		}

		return $rsp;
	}

	#################################################################

?>
