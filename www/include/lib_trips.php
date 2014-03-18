<?php

	loadlib("whereonearth");

	########################################################################
	
	function trips_travel_type_map($string_keys=0){

		$map = array(
			0 => 'none of your business',
			10 => 'on foot',
			1 => 'bicycle',
			2 => 'car',
			11 => 'motorcycle',
			3 => 'bus',
			4 => 'train',
			5 => 'boat',
			6 => 'plane',
			7 => 'helicopter',
			8 => 'force of will',
			9 => 'any means necessary',
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


		$rsp = whereonearth_fetch_woeid($trip['locality_id']);

		if (! $rsp['ok']){
			return array('ok' => 0, 'error' => 'Failed to retrieve locality data');
		}

		$loc = $rsp['data'];

		$rsp = privatesquare_utils_generate_id();

		if (! $rsp['ok']){
			return array('ok' => 0, 'error' => 'Failed to generate trip ID');
		}

		$trip['id'] = $rsp['id'];
		$trip['created'] = $now;

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

	function trips_update_trip(&$trip, $update){

		$user = users_get_by_id($trip['user_id']);
		$cluster = $user['cluster_id'];

		$insert = array();

		foreach ($update as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$enc_id = AddSlashes($trip['id']);
		$where = "id='{$enc_id}'";

		$rsp = db_update_users($cluster, 'Trips', $insert, $where);

		if ($rsp['ok']){
			$trip = array_merge($trip, $update);
			$rsp['trip'] = $trip;
		}

		return $rsp;
	}

	########################################################################

	function trips_delete_trip(&$trip){

		$user = users_get_by_id($trip['user_id']);
		$cluster = $user['cluster_id'];

		$enc_id = AddSlashes($trip['id']);
		$sql = "DELETE FROM Trips WHERE id='{$enc_id}'";

		$rsp = db_write_users($cluster, $sql);
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
			'when' => null,
			'where' => null,
			'woeid' => null,
			'exclude_trip' => null,
			'year' => null,
			'month' => null,
		);

		$more = array_merge($defaults, $more);

		$cluster_id = $user['cluster_id'];
		$enc_id = AddSlashes($user['id']);

		$sql = array();

		$sql[] = "SELECT * FROM Trips WHERE user_id='{$enc_id}'";

		if ($more['when'] == 'past'){
			$sql[] = "AND `departure` <= NOW()";
		}

		if (($more['where']) && ($more['woeid'])){

			$col = $more['where'] . '_id';

			$enc_col = AddSlashes($col);
			$enc_id = AddSlashes($more['woeid']);

			$sql[] = "AND `{$enc_col}`='{$enc_id}'";

			# IN (trip ids) ?

			if ($more['exclude_trip']){
				$enc_trip = AddSlashes($more['exclude_trip']);
				$sql[] = " AND id != '{$enc_trip}'";			
			}
		}

		if (($more['year']) && ($more['month'])){

			$days = cal_days_in_month(CAL_GREGORIAN, $more['month'], $more['year']);

			$start = "{$more['year']}-{$more['month']}-01";
			$end = "{$more['year']}-{$more['month']}-{$days}";

			$enc_start = AddSlashes($start);
			$enc_end = AddSlashes($end);

			# TO DO: figure out trips that span a single month (20140118/straup)

			$conditions = array();

			$conditions[] = "`arrival` BETWEEN '{$enc_start}' AND '{$enc_end}'";
			$conditions = implode(" OR ", $conditions);

			$sql[] = "AND ({$conditions})";
		}

		if ($more['year']){

			$start = "{$more['year']}-01-01";
			$end = "{$more['year']}-12-31";

			$enc_start = AddSlashes($start);
			$enc_end = AddSlashes($end);

			# TO DO: figure out trips that span a single year (20140118/straup)

			$conditions = array();

			$conditions[] = "`arrival` BETWEEN '{$enc_start}' AND '{$enc_end}'";
			$conditions = implode(" OR ", $conditions);

			$sql[] = "AND ({$conditions})";
		}

		if ($more['when'] == 'upcoming'){
			$sql[] = "AND `departure` >= NOW()";
		}

		if ($more['when'] == 'past'){
			$sql[] = "ORDER BY arrival DESC, departure DESC";
		}

		else if ($more['where']){
			$sql[] = "ORDER BY arrival DESC, departure DESC";
		}

		else {
			$sql[] = "ORDER BY arrival, departure";
		}

		$sql = implode(" ", $sql);
		# dumper($sql);

		$rsp = db_fetch_paginated_users($cluster_id, $sql, $more);

		return $rsp;
	}

	########################################################################

	function trips_get_places_for_user(&$user, $more=array()){

		$enc_user = AddSlashes($user['id']);
		$cluster = $user['cluster_id'];

		$sql = array();

		$sql[] = "SELECT locality_id, COUNT(id) AS count_trips FROM Trips";
		$sql[] = "WHERE user_id='{$enc_user}'";
		$sql[] = "GROUP BY locality_id ORDER BY count_trips DESC";

		$sql = implode(" ", $sql);

		$rsp = db_fetch_users($cluster, $sql, $more);
		return $rsp;
	}

	########################################################################

	function trips_stats_for_user(&$user){

		$stats = array();

		$enc_user = AddSlashes($user['id']);
		$cluster = $user['cluster_id'];

		$sql = "SELECT YEAR(arrival) AS year, COUNT(id) AS trips FROM Trips WHERE user_id='{$enc_user}' GROUP BY year ORDER BY year";
		$rsp = db_fetch_users($cluster, $sql);

		foreach ($rsp['rows'] as $row){
			$stats[$row['year']] = array('trips' => $row['trips']);
		}

		foreach ($stats as $year => $details){

			$stats[$year]['cities'] = array();

			$enc_year = AddSlashes($year);
			$sql = "SELECT locality_id, COUNT(id) AS count_trips FROM Trips WHERE user_id='{$enc_user}' AND YEAR(arrival) = '{$enc_year}' GROUP BY locality_id ORDER BY count_trips DESC";

			$rsp = db_fetch_users($cluster, $sql);

			foreach ($rsp['rows'] as $row){
				$stats[$year]['cities'][$row['locality_id']] = $row['count_trips'];
			}
		}

		return array('ok' => 1, 'stats' => $stats);
	}

	########################################################################

	function trips_inflate_trip(&$trip){

		$trip['latitude'] = $locality['latitude'];
		$trip['longitude'] = $locality['longitude'];

		$arrival_ts = strtotime($trip['arrival']);
		$departure_ts = strtotime($trip['departure']);

		$now = time();

		$trip['arrival_ts'] = $arrival_ts;
		$trip['arrival_past'] = ($arrival_ts < $now) ? 1 : 0;

		$trip['departure_ts'] = $departure_ts;
		$trip['departure_past'] = ($departure_ts < $now) ? 1 : 0;

		$user = users_get_by_id($trip['user_id']);
		$trip['user'] = $user;
	}

	########################################################################

	# the end
