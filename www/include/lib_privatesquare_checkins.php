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

		if (! isset($checkin['created'])){
			$checkin['created'] = time();
		}

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

	# Here's the thing: This will probably need to be cached and added
	# to incrementally at some point in the not too distant future. How
	# that's done remains an open question. MySQL blob? Write to disk?
	# Dunno. On the other hand we're just going to enjoy not having to
	# think about it for the moment. KTHXBYE (20120226/straup)

	function privatesquare_checkins_export_for_user(&$user){

		$rows = array();

		$count_pages = null;

		$args = array(
			'page' => 1,
			'per_page' => 100,
		);

		while ((! isset($count_pages)) || ($args['page'] <= $count_pages)){

			if (! isset($count_pages)){
				$count_pages = $rsp['pagination']['page_count'];
			}

			# per the above we may need to add a flag to *not* fetch
			# the full venue listing out of the database (20120226/straup)

			$rsp = privatesquare_checkins_for_user($user, $args);
			$rows = array_merge($rows, $rsp['rows']);

			$args['page'] += 1;
		}

		return okay(array('rows' => $rows));
	}

 	#################################################################

	function privatesquare_checkins_for_user_nearby(&$user, $lat, $lon, $more=array()){

		loadlib("geo_utils");
		
		$dist = (isset($more['dist'])) ? floatval($more['dist']) : 0.2;
		$unit = (geo_utils_is_valid_unit($more['unit'])) ? $more['unit'] : 'm';

		# TO DO: sanity check to ensure max $dist

		$bbox = geo_utils_bbox_from_point($lat, $lon, $dist, $unit);

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		$sql = "SELECT venue_id, COUNT(id) AS count FROM PrivatesquareCheckins WHERE user_id='{$enc_user}'";
		$sql .= " AND latitude BETWEEN {$bbox[0]} AND {$bbox[2]} AND longitude BETWEEN {$bbox[1]} AND {$bbox[3]}";
		$sql .= " GROUP BY venue_id";

		$rsp = db_fetch_users($cluster_id, $sql, $more);

		if (! $rsp['ok']){
			return $rsp;
		}

		$tmp = array();

		foreach ($rsp['rows'] as $row){
			$tmp[$row['venue_id']] = $row['count'];
		}

		arsort($tmp);

		$venues = array();

		foreach ($tmp as $venue_id => $count){
			$venue = foursquare_venues_get_by_venue_id($venue_id); 
			$venue['count_checkins'] = $count;
			$venues[] = $venue;
		}

		return okay(array('rows' => $venues));
	}

 	#################################################################

	# Note the need to pass $user because we don't have a lookup
	# table for checkin IDs, maybe we should... (20120218/straup)

	function privatesquare_checkins_get_by_id(&$user, $id){

		if (is_numeric($id)){
			return privatesquare_checkins_get_by_privatesquare_id($user, $id);
		}

		return privatesquare_checkins_get_by_foursquare_id($user, $id);
	}

 	#################################################################

	function privatesquare_checkins_get_by_privatesquare_id(&$user, $id){

		$cluster_id = $user['cluster_id'];

		$enc_user = AddSlashes($user['id']);
		$enc_id = AddSlashes($id);

		$sql = "SELECT * FROM PrivatesquareCheckins WHERE user_id='{$enc_user}' AND id='{$enc_id}'";
		return db_single(db_fetch_users($cluster_id, $sql));
	}

 	#################################################################

	function privatesquare_checkins_get_by_foursquare_id(&$user, $id){

		$cluster_id = $user['cluster_id'];

		$enc_user = AddSlashes($user['id']);
		$enc_id = AddSlashes($id);

		$sql = "SELECT * FROM PrivatesquareCheckins WHERE user_id='{$enc_user}' AND checkin_id='{$enc_id}'";
		return db_single(db_fetch_users($cluster_id, $sql));
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
