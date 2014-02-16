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

		# get trips here...
	}

	########################################################################

	function trips_calendars_add_calendar($calendar){

		$user = users_get_by_id($calendar['user_id']);
		$cluster = $user['id'];

		$rsp = privatesquare_utils_generate_id();

		if (! $rsp['ok']){
			return array('ok' => 0, 'error' => 'Failed to generate trip ID');
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

	}

	########################################################################

	function trips_calendars_generate_hash(){

		$rand = random_string();
		$now = time();

		$key = implode(".", array($now, $rand));
		return md5($key);
	}

	########################################################################

	# the end
