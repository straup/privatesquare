<?php

	loadlib("random");

	# See all of this? I'm still sorting out federating the user-y bits...
	# (20140130/straup)

	########################################################################

	function trips_calendars_get_by_id($id){

		$enc_id = AddSlashes($id);

		$sql = "SELECT * FROM TripsCalendars WHERE id='{$enc_id}'";
		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	########################################################################

	function trips_calendars_get_by_hash($hash){

		$enc_hash = AddSlashes($hash);

		$sql = "SELECT * FROM TripsCalendars WHERE hash='{$enc_hash}'";
		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	########################################################################

	function trips_calendars_get_for_user(&$user, $more=array()){

		$enc_user = AddSlashes($user['id']);
		$sql = "SELECT * FROM TripsCalendars WHERE user_id='{$enc_user}' AND deleted=0 ORDER BY created DESC";

		$rsp = db_fetch($sql, $more);
		return $rsp;
	}

	########################################################################

	function trips_calendars_get_trips(&$calendar){

		$user = users_get_by_id($calendar['user_id']);

		$more = array(
			'when' => 'upcoming',
			'per_page' => 100,
		);

		if ($woeid = $calendar['locality']){
			$more['where'] = $locality;
			$more['woeid'] = $woeid;
		}

		if (($calendar['include_past']) && (features_is_enabled("trips_calendars_include_past"))){
			$more['when'] = 'all';
		}

		# kind of trip (status_id)

		if ($id = $calendar['status_id']){
			$more['status'] = $id;
		}

		# See what's going on here? Potentially someone might have 10K+ 
		# trips in a single calendar at which point fetching them all in
		# a single query would be bad but I am going to wait until that
		# actually happens before worrying about it. (20140420/straup)

		if (($calendar['include_past']) && (features_is_enabled("trips_calendars_include_past"))){

			$more['per_page'] = 1;

			$rsp = trips_get_for_user($user, $more);

			if (! $rsp['ok']){
				return $rsp;
			}

			$total = $rsp['pagination']['total_count'];
			$more['per_page'] = $total;
		}


		$rsp = trips_get_for_user($user, $more);
		return $rsp;
	}

	########################################################################

	function trips_calendars_add_calendar($calendar){

		$user = users_get_by_id($calendar['user_id']);
		$cluster = $user['id'];

		$rsp = privatesquare_utils_generate_id();

		if (! $rsp['ok']){
			return array('ok' => 0, 'error' => 'Failed to generate calendar ID');
		}

		$calendar['id'] = $rsp['id'];

		$hash = trips_calendars_generate_hash();
		$calendar['hash'] = $hash;

		$now = time();
		$calendar['created'] = $now;

		$insert = array();

		foreach ($calendar as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert('TripsCalendars', $insert);

		if ($rsp['ok']){
			$rsp['calendar'] = $calendar;
		}

		return $rsp;
	}

	########################################################################

	function trips_calendars_delete_calendar(&$calendar){

		$now = time();

		$update = array(
			'deleted' => $now,
		);

		$rsp = trips_calendars_update_calendar($calendar, $update);
		return $rsp;
	}

	########################################################################

	function trips_calendars_update_calendar(&$calendar, $update){

		$user = users_get_by_id($calendar['user_id']);
		$cluster = $user['id'];

		$now = time();
		$update['lastmodified'] = $now;

		$insert = array();

		foreach ($update as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$enc_id = AddSlashes($calendar['id']);
		$where = "id='{$enc_id}'";

		$rsp = db_update('TripsCalendars', $insert, $where);

		if ($rsp['ok']){

			$calendar = array_merge($calendar, $update);
			$rsp['calendar'] = $calendar;
		}

		return $rsp;
	}

	########################################################################

	function trips_calendars_inflate_calendar(&$calendar){

		if ($woeid = $calendar['locality_id']){

			$rsp = whereonearth_fetch_woeid($woeid);
			$locality = $rsp['data'];
			$calendar['locality'] = $locality;
		}

		$now = time();

		$expired = (($calendar['expires']) && ($calendar['expires'] < $now)) ? 1 : 0;
		$calendar['is_expired'] = $expired;
	}

	########################################################################

	function trips_calendars_generate_hash(){

		$rand = random_string();
		$now = time();

		$key = implode(".", array($now, $rand));
		return md5($key);
	}

	########################################################################

	function trips_calendars_share_url(&$calendar){

		$fsq_user = foursquare_users_get_by_user_id($calendar['user_id']);

		$enc_user = urlencode($fsq_user['foursquare_id']);
		$enc_calendar = urlencode($calendar['id']);

		return "{$GLOBALS['cfg']['abs_root_url']}user/{$enc_user}/trips/calendars/{$enc_calendar}/share/";
	}

	########################################################################

	function trips_calendars_ics_url(&$calendar){

		$fsq_user = foursquare_users_get_by_user_id($calendar['user_id']);

		$enc_user = urlencode($fsq_user['foursquare_id']);
		$enc_hash = urlencode($calendar['hash']);

		return "{$GLOBALS['cfg']['abs_root_url']}user/{$enc_user}/trips/calendars.ics?c={$enc_hash}";

	}

	########################################################################

	# the end
