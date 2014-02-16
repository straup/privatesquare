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

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$add_crumb = crumb_generate("api", "privatesquare.trips.calendars.addCalendar");
	$GLOBALS['smarty']->assign("add_calendar_crumb", $add_crumb);

	$GLOBALS['smarty']->display("page_user_trips_calendars_add.txt");
	exit();

?>
