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

	function trips_travel_status_map($string_keys=0){

		$map = array(
			0 => 'tentative',
			1 => 'confirmed',
			2 => 'wishful thinking',
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

	function trips_is_valid_status_id($id){
		$map = trips_travel_status_map();
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

		$loc = geo_flickr_get_woeid($trip['locality_id']);

		$tz = timezones_get_by_tzid($loc['timezone']);
		$trip['timezone_id'] = $tz['woeid'];

		$region = $loc['region'];
		$trip['region_id'] = $region['woeid'];

		$country = $loc['country'];
		$trip['country_id'] = $country['woeid'];

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

	# SEE THIS: IT MEANS WE NEED TO FIGURE OUT WHERE WE STORE Trips...

	function trips_get_by_id($id){

		$enc_id = AddSlashes($id);

		$sql = "SELECT * FROM Trips WHERE id='{$enc_id}'";
		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	########################################################################

	# SEE THIS: IT MEANS WE NEED TO FIGURE OUT WHERE WE STORE Trips...

	function trips_get_by_dopplr_id($id){

		$enc_id = AddSlashes($id);

		$sql = "SELECT * FROM Trips WHERE dopplr_id='{$enc_id}'";
		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	########################################################################

	# TO DO: database indexes (20140118/straup)

	function trips_get_for_user(&$user, $more=array()){

		$defaults = array(
			'when' => 'upcoming',
			'year' => null,
			'month' => null,
		);

		$more = array_merge($defaults, $more);

		$cluster_id = $user['cluster_id'];
		$enc_id = AddSlashes($user['id']);

		$sql = array();

		$sql[] = "SELECT * FROM Trips WHERE user_id='{$enc_id}'";

		if ($more['when'] == 'past'){
			$sql[] = "AND departure <= NOW()";
		}

		else if (($more['year']) && ($more['month'])){

			$days = cal_days_in_month(CAL_GREGORIAN, $more['month'], $more['year']);

			$start = "{$more['year']}-{$more['month']}-01";
			$end = "{$more['year']}-{$more['month']}-{$days}";

			$enc_start = AddSlashes($start);
			$enc_end = AddSlashes($end);

			# TO DO: figure out trips that span a single month (20140118/straup)

			$conditions = array();

			$conditions[] = "arrival BETWEEN '{$enc_start}' AND '{$enc_end}'";
			$conditions = implode(" OR ", $conditions);

			$sql[] = "AND ({$conditions})";
		}

		else if ($more['year']){

			$start = "{$more['year']}-01-01";
			$end = "{$more['year']}-12-31";

			$enc_start = AddSlashes($start);
			$enc_end = AddSlashes($end);

			# TO DO: figure out trips that span a single year (20140118/straup)

			$conditions = array();

			$conditions[] = "arrival BETWEEN '{$enc_start}' AND '{$enc_end}'";
			$conditions = implode(" OR ", $conditions);

			$sql[] = "AND ({$conditions})";
		}

		else {
			$sql[] = "AND departure >= NOW()";
		}

		$sql[] = "ORDER BY arrival, departure DESC";

		$sql = implode(" ", $sql);

		$rsp = db_fetch_paginated_users($cluster_id, $sql, $more);

		return $rsp;
	}

	########################################################################

	function trips_inflate_trip(&$trip){

		$locality = geo_flickr_get_woeid($trip['locality_id']);
		$trip['locality'] = $locality;

		$arrival_ts = strtotime($trip['arrival']);
		$departure_ts = strtotime($trip['departure']);

		$now = time();

		$trip['arrival_ts'] = $arrival_ts;
		$trip['arrival_past'] = ($arrival_ts < $now) ? 1 : 0;

		$trip['departure_ts'] = $arrival_ts;
		$trip['departure_past'] = ($arrival_ts < $now) ? 1 : 0;

		$user = users_get_by_id($trip['user_id']);
		$trip['user'] = $user;
	}

	########################################################################

	# the end
