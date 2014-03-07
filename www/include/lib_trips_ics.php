<?php

	# https://tools.ietf.org/html/rfc5545
	# http://www.kanzaki.com/docs/ical/vevent.html

	########################################################################

	function trips_ics_export(&$trips, $fh, $more=array()){

		$defaults = array(
			'calname' => 'privatesquare',
		);

		$more = array_merge($defaults, $more);

		$GLOBALS['smarty']->assign("calname", $more['calname']);

		$begin = $GLOBALS['smarty']->fetch("inc_ics_vcalendar_begin.txt");
		fwrite($fh, $begin);

		foreach ($trips as $row){
			trips_ics_export_row($row, $fh, $more);
		}

		$end = $GLOBALS['smarty']->fetch("inc_ics_vcalendar_end.txt");
		fwrite($fh, $end);
	}

	########################################################################

	function trips_ics_export_row($row, $fh, $more=array()){

		$vevent = trips_ics_to_vevent($row);

		$GLOBALS['smarty']->assign_by_ref("vevent", $vevent);
		$ics = $GLOBALS['smarty']->fetch("inc_ics_vevent.txt");

		fwrite($fh, $ics);
	}

	########################################################################

	function trips_ics_to_vevent(&$trip){

		$tz = timezones_get_by_woeid($trip['timezone_id']);
		$tzid = $tz['tzid'];

		$arrival = str_replace("-", "", $trip['arrival']);
		$departure = str_replace("-", "", $trip['departure']);

		# Yup. We're really doing this. Apparently it's required
		# in the spec... (20140306/straup)

		$dt = new DateTime($departure);
		$dt->add(new DateInterval('P1D'));

		$departure = $dt->format('Ymd');

		$status_map = trips_travel_status_map();
		$travel_map = trips_travel_type_map();

		$status = $status_map[$trip['status_id']];

		$status = str_replace(" ", "", $status);
		$status = strtoupper($status);

		$event = array(
			'id' => "x-urn:privatesquare:trip:{$trip['id']}",
			'created' => $trip['created'],
			'summary' => "trip #{$trip['id']}",
			'latitude' => $trip['latitude'],
			'longitude' => $trip['longitude'],
			'start' => $arrival,
			'end' => $departure,
			'tzid' => $tzid,
			'status' => $status,
		);

		if ($trip['locality']){
			$event['summary'] = $trip['locality']['woe_name'];
			$event['location'] = $trip['locality']['name'];
		}

		$description = array(
			$status_map[$trip['status_id']],
		);

		if ($trip['note']){
			$description[] = str_replace("\n", "\\n", $trip['note']);
		}

		/*
		if ($id = $trip['arrive_by_id']){
			$description[] = "arriving by {$travel_map[$id]}";
		}

		if ($id = $trip['depart_by_id']){
			$description[] = "departing by {$travel_map[$id]}";
		}
		*/

		$event['description'] = implode("\\n\\n", $description);

		return $event;
	}

	########################################################################

	# the end
