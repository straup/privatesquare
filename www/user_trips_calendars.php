<?php

	include("include/init.php");
	loadlib("trips");
	loadlib("trips_calendars");

	features_ensure_enabled(array(
		"trips", "trips_calendars"
	));

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $user);

	$more = array();

	$rsp = trips_calendars_get_for_user($user, $more);	
	$calendars = array();

	foreach ($rsp['rows'] as $row){
		trips_calendars_inflate_calendar($row);
		$calendars[] = $row;
	}

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$add_crumb = crumb_generate("api", "privatesquare.trips.calendars.addCalendar");
	$GLOBALS['smarty']->assign("add_calendar_crumb", $add_crumb);

	$GLOBALS['smarty']->display("page_user_trips_calendars.txt");
	exit();

?>
