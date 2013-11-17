<?php

	#################################################################

	function venues_get_by_venue_id($id){

		$enc_id = AddSlashes($id);
		$sql = "SELECT * FROM Venues WHERE venue_id='{$enc_id}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	#################################################################

	function venues_get_by_venue_id_for_provider($venue_id, $provider_id){

		$enc_provider = AddSlashes($provider_id);
		$enc_venue = AddSlashes($venue_id);

		$sql = "SELECT * FROM Venues WHERE provider_id='{$enc_provider}' AND provider_venue_id='{$enc_venue}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	#################################################################

	function venues_archive_venue_for_provider($venue_id, $provider_id){

		if ($venue = venues_get_by_venue_id_for_provider($venue_id, $provider_id)){

			return array('ok' => 1, 'venue' => $venue);
		}

		$map = venues_providers_map();
		$provider = $map[$provider_id];

		if ($provider == 'foursquare'){

			$rsp = venues_foursquare_fetch_venue($venue_id);

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
		}

		else if ($provider == 'stateofmind'){

			$data = venues_stateofmind_fetch_venue($venue_id);

			$venue = array(
				'venue_id' => $data['id'],
				'name' => $data['name'],
				'data' => json_encode($data),
			);
		}

		else {
			return array('ok' => 0, 'error' => 'Unknown provider');
		}

		# See this? Not sure this is the best way to deal with the issue of
		# 'place-less' places having a multiplicity of geographies...
		# (20131117/straup)

		if ($provider != 'stateofmind'){

			if ((isset($venue['latitude'])) && (isset($venue['longitude']))){
				venues_geo_append_hierarchy($venue['latitude'], $venue['longitude'], $venue);
			}
		}

		$venue['provider_id'] = $provider_id;
		$venue['provider_venue_id'] = $venue_id;

		$rsp = venues_add_venue($venue);
		return $rsp;
	}

	#################################################################

	function venues_add_venue($venue){

		$rsp = privatesquare_utils_generate_id(64);

		if (! $rsp['ok']){
			return $rsp;
		}

		$venue['venue_id'] = $rsp['id'];

		$insert = array();

		foreach ($venue as $k => $v){
			$insert[$k] = AddSlashes($v);
		}	

		$now = time();

		$on_dupe = array(
			'last_checkin' => AddSlashes($now)
		);

		$rsp = db_insert('Venues', $insert);

		if ($rsp['ok']){
			$rsp['venue'] = $venue;
		}

		return $rsp;
	}

	#################################################################

	# the end
