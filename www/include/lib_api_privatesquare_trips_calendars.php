<?php

	loadlib("trips_calendars");

	########################################################################

	function api_privatesquare_trips_calendars_addCalendar(){


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

		if ($note = post_str("notes")){
			$calendar['note'] = $note;
		}

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

	# the end
