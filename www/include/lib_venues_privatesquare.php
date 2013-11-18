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
