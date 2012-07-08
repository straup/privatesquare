<?php

	loadlib("foursquare_venues");
	loadlib("datetime_when");
	loadlib("reverse_geoplanet");

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

	# Dunno... might move in to a separate library (20120705/straup)

	function privatesquare_checkins_list_map($string_keys=0){

		$status_map = privatesquare_checkins_status_map();
		$list_map = array();

		foreach ($status_map as $id => $label){

			if (in_array($id, array(0, 1))){
				continue;
			}

			$clean = str_replace(" ", "", $label);
			$list_map[$id] = $clean;
		}

		if ($string_keys){
			$list_map = array_flip($list_map);
		}

		return $list_map;
	}

 	#################################################################

	function privatesquare_checkins_is_valid_status($status_id){
		$map = privatesquare_checkins_status_map();
		return (isset($map[$status_id])) ? 1 : 0;
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

	function privatesquare_checkins_update(&$checkin, $update){

		$user = users_get_by_id($checkin['user_id']);
		$cluster_id = $user['cluster_id'];

		# requires a schema change (20120703/straup)
		# $update['last_modified'] = time();

		$insert = array();

		foreach ($update as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$enc_id = AddSlashes($checkin['id']);
		$where = "id='{$enc_id}'";

		$rsp = db_update_users($cluster_id, 'PrivatesquareCheckins', $insert, $where);
		return $rsp;
	}

 	#################################################################

	function privatesquare_checkins_for_user(&$user, $more=array()){

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		$sql = "SELECT * FROM PrivatesquareCheckins WHERE user_id='{$enc_user}'";

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

		else if (isset($more['locality'])){
			$enc_locality = AddSlashes($more['locality']);
			$sql .= " AND locality='{$enc_locality}'";
		}

		$sql .= " ORDER BY created DESC";

		$rsp = db_fetch_paginated_users($cluster_id, $sql, $more);

		if (! $rsp['ok']){
			return $rsp;
		}

		$count = count($rsp['rows']);

		for ($i=0; $i < $count; $i++){
			privatesquare_checkins_inflate_extras($rsp['rows'][$i], $more);
		}

		return $rsp;
	}

 	#################################################################

	function privatesquare_checkins_statuses_for_user(&$user, $more=array()){

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		$sql = "SELECT status_id, COUNT(id) AS cnt FROM PrivatesquareCheckins WHERE user_id='{$enc_user}'";

		if (isset($more['venue_id'])){
			$enc_venue = AddSlashes($more['venue_id']);
			$sql .= " AND venue_id='{$enc_venue}'";
		}

		$sql .= " GROUP BY status_id";

		$rsp = db_fetch_users($cluster_id, $sql);

		$stats = array();

		foreach ($rsp['rows'] as $row){
			$stats[$row['status_id']] = $row['cnt'];
		}

		return $stats;
	}

 	#################################################################

	function privatesquare_checkins_localities_for_user(&$user, $more=array()){

		$defaults = array(
			'page' => 1,
			'per_page' => 10,
		);

		$more = array_merge($defaults, $more);

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		# TO DO: indexes

		# Note: we do pagination in memory because we're going to
		# sort the results by count; this all happens below.

		$sql = "SELECT locality, COUNT(id) AS count FROM PrivatesquareCheckins WHERE user_id='{$enc_user}' GROUP BY locality";
		$rsp = db_fetch_users($cluster_id, $sql);

		if (! $rsp['ok']){
			return $rsp;
		}

		$tmp = array();

		foreach ($rsp['rows'] as $row){

			if (! $row['locality']){
				continue;
			}

			$tmp[$row['locality']] = $row['count'];
		}

		arsort($tmp);

		$woeids = array_keys($tmp);
		$total_count = count($woeids);

		#

		$page_count = ceil($total_count / $more['per_page']);
		$last_page_count = $total_count - (($page_count - 1) * $more['per_page']);

		$pagination = array(
			'total_count' => $total_count,
			'page' => $more['page'],
			'per_page' => $more['per_page'],
			'page_count' => $page_count,
		);

		if ($GLOBALS['cfg']['pagination_assign_smarty_variable']){
			$GLOBALS['smarty']->assign('pagination', $pagination);
		}

		#

		$offset = $more['per_page'] * ($more['page'] - 1);
		$woeids = array_slice($woeids, $offset, $more['per_page']);

		$localities = array();

		foreach ($woeids as $woeid){

			$count = $tmp[$woeid];

			$row = reverse_geoplanet_get_by_woeid($woeid, 'locality');

			# what if ! $row? should never happen but...

			$row['count'] = $count;

			# Maybe always get this? Filtering may just be a
			# pointless optimization (20120229/straup)

			if ($count > 1){

				$venues_more = array(
					'locality' => $woeid,
					'stats_mode' => 1,
				);

				$venues_rsp = privatesquare_checkins_venues_for_user($user, $venues_more);
				$row['venues'] = $venues_rsp['rows'];
			}

			$localities[] = $row;
		}

		return okay(array(
			'rows' => $localities,
			'pagination' => $pagination,
		));
	}

 	#################################################################

	function privatesquare_checkins_venues_for_user_and_status(&$user, $status_id, $more=array()){

		$defaults = array(
			'stats_mode' => 0,
			'per_page' => 10,
			'page' => 1,
			'dist' => .5
		);

		$more = array_merge($defaults, $more);

		$cluster_id = $user['cluster_id'];

		$enc_user = AddSlashes($user['id']);
		$enc_status = AddSlashes($status_id);

		# TO DO: indexes
		# TO DO: check for nearby/geo

		$sql = "SELECT * FROM PrivatesquareCheckins WHERE user_id='{$enc_user}' AND status_id='{$enc_status}'";

		if (isset($more['locality'])){
			$enc_loc = AddSlashes($more['locality']);
			$sql .= " AND locality='{$enc_loc}'";
		}

		else if ((isset($more['latitude'])) && (isset($more['longitude']))){

			loadlib("geo_utils");
		
			$dist = $more['dist'];
			$unit = 'm';

			# TO DO: sanity check to ensure max $dist

			$bbox = geo_utils_bbox_from_point($more['latitude'], $more['longitude'], $dist, $unit);
			$sql .= " AND latitude BETWEEN {$bbox[0]} AND {$bbox[2]} AND longitude BETWEEN {$bbox[1]} AND {$bbox[3]}";
		}

		$sql .= " ORDER BY created DESC";

		$rsp = db_fetch_paginated_users($cluster_id, $sql, $more);

		if (! $rsp['ok']){
			return $rsp;
		}

		$rows = array();

		foreach ($rsp['rows'] as $row){

			$_more = array(
				'inflate_locality' => 1,
			);

			privatesquare_checkins_inflate_extras($row, $_more);

			# TO DO: OMG... INDEXES

			$enc_venue = AddSlashes($row['venue_id']);
			$enc_created = AddSlashes($row['created']);

			$_sql = "SELECT COUNT(id) AS cnt FROM PrivatesquareCheckins WHERE user_id='{$enc_user}'";

			if ($status_id == 2){
				$_sql .= " AND status_id != '{$enc_status}'";
			}

			$_sql .= " AND venue_id='{$enc_venue}'";
			$_sql .= " AND created > '{$enc_created}'";

			$_rsp = db_single(db_fetch_users($cluster_id, $_sql));

			$row['count_checkins'] = $_rsp['cnt'];
			$rows[] = $row;
		}

		$rsp['rows'] = $rows;
		return $rsp;
	}

 	#################################################################

	# TO DO: venues for status (notes)
	# this should probably be it's own function because while it
	# maybe should group on venue_id the requirements for how things
	# are sorted are different and it's quickly turning in to a mess
	# of if/else statements. See also, comments below about filesorts
	# (20120701/straup)

	function privatesquare_checkins_venues_for_user(&$user, $more=array()){

		$defaults = array(
			'stats_mode' => 0,
			'per_page' => 10,
			'page' => 1,
		);

		$more = array_merge($defaults, $more);

		# TO DO: date ranges

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		$sql = "SELECT venue_id, COUNT(id) AS count FROM PrivatesquareCheckins WHERE user_id='{$enc_user}'";

		# TO DO: indexes so we can do status for user and city...

		if (isset($more['locality'])){
			$enc_loc = AddSlashes($more['locality']);
			$sql .= " AND locality='{$enc_loc}'";
		}

		$sql .= " GROUP BY venue_id";

		$rsp = db_fetch_users($cluster_id, $sql);

		$tmp = array();

		foreach ($rsp['rows'] as $row){
			$tmp[$row['venue_id']] = $row['count'];
		}

		arsort($tmp);

		$venue_ids = array_keys($tmp);
		$total_count = count($venue_ids);

		$rows = array();
		$pagination = array();

		if ($more['stats_mode']){
			# do not paginate
		}

		else {

			$page_count = ceil($total_count / $more['per_page']);
			$last_page_count = $total_count - (($page_count - 1) * $more['per_page']);

			$pagination = array(
				'total_count' => $total_count,
				'page' => $more['page'],
				'per_page' => $more['per_page'],
				'page_count' => $page_count,
			);

			if ($GLOBALS['cfg']['pagination_assign_smarty_variable']){
				$GLOBALS['smarty']->assign('pagination', $pagination);
			}

			$offset = $more['per_page'] * ($more['page'] - 1);
			$venue_ids = array_slice($venue_ids, $offset, $more['per_page']);
		}

		foreach ($venue_ids as $venue_id){

			$count = $tmp[$venue_id];

			if ($more['stats_mode']){

				$venue = array(
					'venue_id' => $venue_id,
				);
			}

			else {
				$venue = foursquare_venues_get_by_venue_id($venue_id);
			}

			$has_visited = privatesquare_checkins_utils_has_visited_venue($user, $venue_id);
			$venue['has_visited'] = $has_visited;

			$venue['count'] = $count;

			$rows[] = $venue;
		}

		return okay(array(
			'rows' => $rows,
			'pagination' => $pagination
		));
	}

 	#################################################################

	function privatesquare_checkins_inflate_extras(&$row, $more=array()){

		$venue_id = $row['venue_id'];
		$venue = foursquare_venues_get_by_venue_id($venue_id); 
		$row['venue'] = $venue;

		if ($row['weather']){

			if ($weather = json_decode($row['weather'], "as hash")){
				$row['weather'] = $weather;
			}
		}

		# This doesn't make any sense unless you've got something
		# like memcache installed. The volume of DB calls that get
		# made relative to the actual use of any data here is out
		# of control. Leaving it as an FYI / nice to have...
		# (20120301/straup)

		if ((isset($more['inflate_locality'])) && ($woeid = $row['locality'])){
			$loc = reverse_geoplanet_get_by_woeid($woeid, 'locality');
		 	$row['locality'] = $loc;
		}

		# note the pass by ref
	}

 	#################################################################

	# Here's the thing: This will probably need to be cached and added
	# to incrementally at some point in the not too distant future. How
	# that's done remains an open question. MySQL blob? Write to disk?
	# Dunno. On the other hand we're just going to enjoy not having to
	# think about it for the moment. KTHXBYE (20120226/straup)

	function privatesquare_checkins_export_for_user(&$user, $more=array()){

		$rows = array();

		$count_pages = null;

		$args = array(
			'page' => 1,
			'per_page' => 100,
		);

		# Note the order of things here: don't overwrite
		# what we've set in $args above

		if (count($more)){
			$args = array_merge($more, $args);
		}

		while ((! isset($count_pages)) || ($args['page'] <= $count_pages)){

			if (! isset($count_pages)){
				$count_pages = $rsp['pagination']['page_count'];
			}

			# per the above we may need to add a flag to *not* fetch
			# the full venue listing out of the database (20120226/straup)

			$rsp = privatesquare_checkins_for_user($user, $args, $more);
			$rows = array_merge($rows, $rsp['rows']);

			$args['page'] += 1;
		}

		return okay(array('rows' => $rows));
	}

 	#################################################################

	function privatesquare_checkins_for_user_nearby(&$user, $lat, $lon, $more=array()){

		loadlib("geo_utils");
		
		$dist = (isset($more['dist'])) ? floatval($more['dist']) : 0.5;
		$unit = (geo_utils_is_valid_unit($more['unit'])) ? $more['unit'] : 'm';

		# TO DO: sanity check to ensure max $dist

		$bbox = geo_utils_bbox_from_point($lat, $lon, $dist, $unit);

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		# TO DO: group by venue_id in memory since the following will always
		# result in a filesort (20120301/straup)

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

			$has_visited = privatesquare_checkins_utils_has_visited_venue($user, $venue_id);
			$venue['has_visited'] = $has_visited;

			$venues[] = $venue;
		}

		return okay(array('rows' => $venues));
	}

 	#################################################################

	# Note the need to pass $user because we don't have a lookup
	# table for checkin IDs, maybe we should... (20120218/straup)

	function privatesquare_checkins_get_by_id(&$user, $id){

		if (is_numeric($id)){
			$row = privatesquare_checkins_get_by_privatesquare_id($user, $id);
		}

		else {
			$row = privatesquare_checkins_get_by_foursquare_id($user, $id);
		}

		if ($row){
			privatesquare_checkins_inflate_extras($row);
		}

		return $row;
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

	function privatesquare_checkins_delete(&$checkin){

		$user = users_get_by_id($checkin['user_id']);
		$cluster_id = $user['cluster_id'];

		$enc_id = AddSlashes($checkin['id']);

		$sql = "DELETE FROM PrivatesquareCheckins WHERE id='{$enc_id}'";

		return db_write_users($cluster_id, $sql);

		# But wait, you say. How does one delete the checkin from
		# foursquare itself. You can't delete checkins via the API
		# because... uh... because, god hates you I guess. So dumb.
		# (20120505/straup)

		# See also:
		# https://groups.google.com/group/foursquare-api/browse_thread/thread/0400eedc66058702

	}

 	#################################################################

	function privatesquare_checkins_bookends_for_date(&$user, $ymd){

		$bookends = array(
			'before' => null,
			'after' => null,
		);

		$fmt = "Y-m-d";

		$cluster_id = $user['cluster_id'];

		$start = strtotime("{$ymd} 00:00:00");
		$stop = strtotime("{$ymd} 23:59:59");

		$enc_user = AddSlashes($user['id']);
		$enc_start = AddSlashes($start);
		$enc_stop = AddSlashes($stop);

		$sql = "SELECT * FROM PrivatesquareCheckins WHERE user_id='{$enc_user}' AND created < {$enc_start} ORDER BY created DESC LIMIT 1";

		if ($row = db_single(db_fetch_users($cluster_id, $sql))){
			$bookends['before'] = date($fmt, $row['created']);
		}

		$sql = "SELECT * FROM PrivatesquareCheckins WHERE user_id='{$enc_user}' AND created > {$enc_stop}";

		if ($row = db_single(db_fetch_users($cluster_id, $sql))){
			$bookends['after'] = date($fmt, $row['created']);
		}

		return $bookends;
	}

 	#################################################################
?>
