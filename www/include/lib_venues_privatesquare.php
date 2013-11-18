<?php

	#################################################################

	function venues_privatesquare_add_venue(&$user, $data){

		$id = privatesquare_utils_generate_artisanal_id();

		if (! $id){
			return array('ok' => 0, 'error' => 'Failed to generate artisanal integer');
		}

		$now = time();

		$venue = array(
			'venue_id' => $id,
			'name' => $data['name'],
			'latitude' => $data['latitude'],
			'longitude' => $data['longitude'],
			'user_id' => $user['id'],
			'provider_id' => 0,
			'provider_venue_id' => $id,
			'created' => $now,
		);

		if ((isset($venue['latitude'])) && (isset($venue['longitude']))){
			venues_geo_append_hierarchy($venue['latitude'], $venue['longitude'], $venue);
		}

		$data = json_encode($data);
		$venue['data'] = $data;

		$insert = array();

		foreach ($venue as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert('Venues', $insert);

		if ($rsp['ok']){

			$venue['url'] = urls_venue($venue);
			$rsp['venue'] = $venue;
		}

		return $rsp;
	}

	#################################################################

	# the end
