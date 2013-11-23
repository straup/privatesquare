<?php

	loadlib("privatesquare_checkins_utils");

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
			'user_id' => $user['id'],
			'provider_id' => 0,
			'provider_venue_id' => $id,
			'created' => $now,
		);

		if (($data['latitude']) && ($data['longitude'])){
			$venue['latitude'] = $data['latitude'];
			$venue['longitude'] = $data['longitude'];
		}

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

	function venues_privatesquare_get_for_user(&$user, $more=array()){

		$enc_user = AddSlashes($user['id']);

		# THIS NEEDS INDEXES (20131121/straup)

		$sql = "SELECT * FROM Venues WHERE provider_id=0 AND user_id='{$enc_user}' ORDER BY created DESC";
		$rsp = db_fetch_paginated($sql, $more);

		$venues = array();

		foreach ($rsp['rows'] as $venue){

			$venue_id = $venue['venue_id'];

			$checkins_more = array(
				'venue_id' => $venue_id,
				'inflate_venue' => 0,
				'inflate_weather' => 0,
			);

			$checkins = privatesquare_checkins_for_user($user, $checkins_more);
			$venue['checkins'] = $checkins['rows'];

			$geo_stats = privatesquare_checkins_utils_geo_stats(array($venue));
			$venue['geo_stats'] = $geo_stats;

			$venues[] = $venue;
		}

		$rsp['rows'] = $venues;
		return $rsp;
	}

	#################################################################

	function venues_privatesquare_search(&$user, $lat, $lon, $more=array()){

		# TO DO: defaults and pagination stuff

		$bbox = geo_utils_bbox_from_point($lat, $lon, .5, $unit='m');

		$enc_bbox = array();

		foreach ($bbox as $coord){
			$enc_bbox[] = AddSlashes($coord);
		}

		$enc_user = AddSlashes($user['id']);

		# TO DO: indexes

		$where = array(
			"(user_id='{$enc_user}' AND latitude IS NULL AND longitude IS NULL)",
			"(user_id='{$enc_user}' AND latitude BETWEEN {$enc_bbox[0]} AND {$enc_bbox[2]} AND longitude BETWEEN {$bbox[1]} AND {$bbox[3]})"
		);

		$where = implode(" OR ", $where);

		$sql = "SELECT * FROM Venues WHERE {$where}";

		$rsp = db_fetch_paginated($sql, $more);
		return $rsp;
	}

	#################################################################

	# the end
