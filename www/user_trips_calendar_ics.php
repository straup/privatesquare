<?php

	include("include/init.php");

	loadlib("trips");
	loadlib("trips_calendars");
	loadlib("trips_ics");

	features_ensure_enabled(array(
		"trips", "trips_calendars"
	));

	$id = get_str("calendar_id");
	$calendar = trips_calendars_get_by_id($id);

	if (! $calendar){
		error_404();
	}

	if ($calendar['deleted']){
		error_410();
	}

	if (($calendar['expires']) && ($now >= $calendar['expires'])){
		error_403();
	}

	$rsp = trips_calendars_get_trips($calendar);
	$trips = array();

	foreach ($rsp['rows'] as $row){
		trips_inflate_trip($row);
		$trips[] = $row;
	}

	# set correct ICS headers here

	$fh = "fix me";

	trips_ics_export($trips, $fh);
	exit();
?>
