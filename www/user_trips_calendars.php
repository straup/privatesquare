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

	$GLOBALS['smarty']->display("page_user_trips_calendars.txt");
	exit();

?>
