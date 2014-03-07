<?php

	########################################################################

	function trips_ics_export(&$trips, $fh, $more=array()){

		$begin = $GLOBALS['smarty']->fetch("page_export_ics_vcalendar_begin.txt");
		fwrite($fh, $begin);

		foreach ($trips as $row){
			trips_ics_export_row($row, $fh, $more);
		}

		$end = $GLOBALS['smarty']->fetch("page_export_ics_vcalendar_end.txt");
		fwrite($fh, $end);
	}

	########################################################################

	function trips_ics_export_row($row, $fh, $more=array()){

		$vevent = trips_ics_to_vevent($row);

		$GLOBALS['smarty']->assign_by_ref("vevent", $vevent);
		$ics = $GLOBALS['smarty']->fetch("page_export_ics_vevent.txt");

		fwrite($fh, $ics);
	}

	########################################################################

	# This is probably still wrong (20140305/straup)

	function trips_ics_to_vevent(&$trip){

		$tz = timezones_get_by_woeid($trip['timezone_id']);
		$tz = new DateTimeZone($tz['tzid']);

		$fmt = "Ymd\THis";

		$start = str_replace("-", "", $trip['arrival']);
		$start .= 'T00:00:00Z';

		$ts = strtotime($start);
		$dt = new DateTime("@$ts");
		$dt->setTimezone($tz);

		$start = $dt->format($fmt);

		$end = str_replace("-", "", $trip['departure']);
		$end .= 'T23:59:59Z';

		$ts = strtotime($end);
		$dt = new DateTime("@$ts");
		$dt->setTimezone($tz);

		$end = $dt->format($fmt);

		$event = array(
			'id' => "x-urn:privatesquare:trip:{$trip['id']}",
			'created' => $trip['created'],
			'name' => 'trip',
			'latitude' => $trip['latitude'],
			'longitude' => $trip['longitude'],
			'start' => $start,
			'end' => $end,
		);

		if ($trip['locality']){
			$event['name'] = $trip['locality']['woe_name'];
			$event['location'] = $trip['locality']['name'];
		}

		# dumper($trip);
		# dumper($event);

		return $event;
	}

	########################################################################

	# the end
