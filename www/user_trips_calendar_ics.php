<?php

	include("include/init.php");

	loadlib("trips");
	loadlib("trips_calendars");
	loadlib("trips_ics");

	features_ensure_enabled(array(
		"trips", "trips_calendars"
	));

	$hash = get_str("c");
	$calendar = trips_calendars_get_by_hash($hash);

	if (! $calendar){
		error_404();
	}

	if ($calendar['deleted']){
		error_410();
	}

	if (($calendar['expires']) && ($now >= $calendar['expires'])){
		error_403();
	}

	# TO DO: check user ID here?

	$rsp = trips_calendars_get_trips($calendar);
	$trips = array();

	foreach ($rsp['rows'] as $row){
		trips_inflate_trip($row);
		$trips[] = $row;
	}

	header("Content-type: text/calendar");
	$fh = fopen('php://output', 'w');

	# TO DO: reconcile with the other ICS export library...

	trips_ics_export($trips, $fh);
	exit();
?>
