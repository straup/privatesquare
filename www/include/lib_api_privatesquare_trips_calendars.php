<?php

	loadlib("trips_calendars");

	########################################################################

	function api_privatesquare_trips_calendars_addCalendar(){

		api_utils_ensure_is_enabled(array(
			"trips", "trips_calendars"
		));

		# $status_id = post_int32("status_id");

		$include_notes = post_int32("include_notes");
		$include_past = post_int32("past_trips");

		$calendar = array(
			'user_id' => $GLOBALS['cfg']['user']['id'],
			'include_notes' => $include_notes,
			'include_past' => $include_past,
		);

		if ($woeid = post_int32("woeid")){
			$calendar['locality_id'] = $woeid;
		}

		if ($note = post_str("note")){
			$calendar['note'] = $note;
		}

		if ($name = post_str("name")){
			$calendar['name'] = $name;
		}

		$expires = post_str("expires");

		if (($expires) && (! preg_match("/^\d{4}-\d{2}-\d{2}$/", $expires))){
			api_output_error(999, "Invalid date format for expires");	
		}

		if ($expires){
			list($y, $m, $d) = explode("-", $expires);

			if (! checkdate($m, $d, $y)){
				api_output_error(999, "Invalid date for expires");
			}

			$expires = strtotime($expires);
			$calendar['expires'] = $expires;
		}

		# api_output_ok($calendar);

		$rsp = trips_calendars_add_calendar($calendar);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		$out = array(
			'calendar' => $rsp['calendar'],
		);

		api_output_ok($out);
	}

	########################################################################

	function api_privatesquare_trips_calendars_editCalendar(){

		api_utils_ensure_is_enabled(array(
			"trips", "trips_calendars"
		));

		$calendar = _api_privatesquare_trips_calendars_get_calendar();
		$update = array();

		# $status_id = post_int32("status_id");

		$include_notes = post_int32("include_notes");

		if ($include_notes != $calendar['include_notes']){
			$update['include_notes'] = $include_notes;
		}

		$include_past = post_int32("past_trips");

		if ($include_past != $calendar['include_past']){
			$update['include_past'] = $include_past;
		}

		# TO DO: sanity check woeid

		$woeid = post_int32("woeid");

		if ($woeid != $calendar['locality_id']){
			$update['locality_id'] = $woeid;
		}

		$note = post_str("note");

		if ($note != $calendar['note']){
			$update['note'] = $note;
		}

		$name = post_str("name");

		if ($name != $calendar['name']){
			$update['name'] = $name;
		}

		# TO DO: sanity check date

		$expires = post_str("expires");

		if (($expires) && (! preg_match("/^\d{4}-\d{2}-\d{2}$/", $expires))){
			api_output_error(999, "Invalid date format for expires");	
		}

		if ($expires){
			list($y, $m, $d) = explode("-", $expires);

			if (! checkdate($m, $d, $y)){
				api_output_error(999, "Invalid date for expires");
			}

			$expires = strtotime($expires);
		}

		if ($expires != $calendar['expires']){
			$update['expires'] = $expires;
		}

		if (! count($update)){
			api_output_error(999, "Nothing to update");
		}

		$rsp = trips_calendars_update_calendar($calendar, $update);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		$calendar = $rsp['calendar'];
		$out = array('calendar' => $calendar);

		api_output_ok($out);
	}

	########################################################################

	function api_privatesquare_trips_calendars_deleteCalendar(){

		api_utils_ensure_is_enabled(array(
			"trips", "trips_calendars"
		));

		$calendar = _api_privatesquare_trips_calendars_get_calendar();

		$rsp = trips_calendars_delete_calendar($calendar);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		api_output_ok();
	}

	########################################################################

	function _api_privatesquare_trips_calendars_get_calendar(){

		$id = post_int64("id");

		if (! $id){
			api_output_error(999, "Missing calendar ID");
		}

		$calendar = trips_calendars_get_by_id($id);

		if (! $calendar){
			api_output_error(999, "Invalid calendar ID");
		}

		if ($calendar['user_id'] != $GLOBALS['cfg']['user']['id']){
			api_output_error(999, "Insufficient permissions");
		}

		return $calendar;
	}

	########################################################################

	# the end
