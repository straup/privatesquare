<?php

	loadlib("foursquare_venues");
	loadlib("datetime_when");

 	#################################################################

	function privatesquare_checkins_status_map($string_keys=0){

		$map = array(
			'0' => 'i am here',
			'1' => 'i was there',
			'2' => 'i want to go there',
			'3' => 'again',
			'4' => 'again again',
			'5' => 'again maybe',
			'6' => 'again never',
		);

		if ($string_keys){
			$map = array_flip($map);
		}

		return $map;
	}

 	#################################################################

	function privatesquare_checkins_create($checkin){

		$user = users_get_by_id($checkin['user_id']);
		$cluster_id = $user['cluster_id'];

		$checkin['id'] = dbtickets_create(64);
		$checkin['created'] = time();

		$insert = array();

		foreach ($checkin as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert_users($cluster_id, 'PrivatesquareCheckins', $insert);

		if ($rsp['ok']){
			$rsp['checkin'] = $checkin;
		}

		return $rsp;
	}

 	#################################################################

	function privatesquare_checkins_for_user(&$user, $more=array()){

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		$sql = "SELECT * FROM PrivatesquareCheckins WHERE user_id='{$enc_user}'";

		# TO DO: indexes

		if (isset($more['when'])){
			list($start, $stop) = datetime_when_parse($more['when']);
			$enc_start = AddSlashes(strtotime($start));
			$enc_stop = AddSlashes(strtotime($stop));

			$sql .= " AND created BETWEEN '{$enc_start}' AND '{$enc_stop}'";
		}

		else if (isset($more['venue_id'])){
			$enc_venue = AddSlashes($more['venue_id']);
			$sql .= " AND venue_id='{$enc_venue}'";
		}

		$sql .= " ORDER BY created DESC";

		$rsp = db_fetch_paginated_users($cluster_id, $sql, $more);

		if (! $rsp['ok']){
			return $rsp;
		}

		$count = count($rsp['rows']);

		for ($i=0; $i < $count; $i++){
			$venue_id = $rsp['rows'][$i]['venue_id'];
			$venue = foursquare_venues_get_by_venue_id($venue_id); 
			$rsp['rows'][$i]['venue'] = $venue;
		}

		return $rsp;
	}

 	#################################################################

	function privatesquare_checkins_venues_for_user(&$user, $more=array()){

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		# please to be caching me; this will always filesort...

		$sql = "SELECT venue_id, COUNT(id) AS cnt FROM PrivatesquareCheckins";
		$sql .= " WHERE user_id='{$enc_user}'";
		$sql .= " GROUP BY venue_id";
		$sql .= " ORDER BY cnt DESC, created DESC";

		$rsp = db_fetch_paginated_users($cluster_id, $sql, $more);

		if (! $rsp['ok']){
			return $rsp;
		}

		$venues = array();

		foreach ($rsp['rows'] as $row){
			$venue = foursquare_venues_get_by_venue_id($row['venue_id']); 
			$venue['count'] = $row['cnt'];
			$venues[] = $venue;
		}

		$rsp['rows'] = $venues;
		return $rsp;
	}

 	#################################################################
?>
