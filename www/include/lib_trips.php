<?php

	########################################################################
	
	function trips_travel_type_map($string_keys=0){

		$map = array(
			0 => 'none of your business',
			1 => 'two wheels',
			2 => 'four wheels',
			3 => 'more wheels',
			4 => 'water vessel',
			5 => 'sky vessel',
			6 => 'space vessel',
			7 => 'force of will',
			8 => 'any means necessary',
		);

		if ($string_keys){
			$map = array_flip($map);
		}

		return $map;
	}

	########################################################################

	function trips_is_valid_travel_type($id){
		$map = trips_travel_type_map();
		return (isset($map[$id])) ? 1 : 0;
	}

	########################################################################

	function trips_add_trip($trip){

		$user = users_get_by_id($trip['user_id']);
		$cluster_id = $user['cluster_id'];

		$now = time();

		$rsp = privatesquare_utils_generate_id();

		if (! $rsp['ok']){
			return array('ok' => 0, 'error' => 'Failed to generate trip ID');
		}

		$trip['id'] = $rsp['id'];
		$trip['created'] = $now;

		#

		$loc = geo_flickr_get_woeid($trip['locality']);

		$tz = timezones_get_by_tzid($loc['timezone']);
		$trip['timezone'] = $tz['woeid'];

		$region = $loc['region'];
		$trip['region'] = $region['woeid'];

		$country = $loc['country'];
		$trip['country'] = $country['woeid'];

		$insert = array();

		foreach ($trip as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_users($cluster_id, 'Trips', $insert);

		if ($rsp['ok']){
			$rsp['trip'] = $trip;
		}

		return $rsp;
	}

	########################################################################

	# the end
