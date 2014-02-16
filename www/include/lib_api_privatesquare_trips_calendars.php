<?php

	loadlib("trips_calendars");

	########################################################################

	function api_privatesquare_trips_calendars_addCalendar(){

		api_utils_ensure_is_enabled(array(
			"trips", "trips_calendars"
		));

		# $status_id = post_int32("status_id");

		$include_notes = (post_isset("include_notes")) ? 1 : 0;
		$include_past = (post_isset("past_trips")) ? 1 : 0;

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
