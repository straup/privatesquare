<?php

	include("include/init.php");
	loadlib("trips");
	loadlib("trips_calendars");

	features_ensure_enabled(array(
		"trips", "trips_calendars"
	));

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];
	$foursquare_user = foursquare_users_get_by_user_id($user['id']);

	$GLOBALS['smarty']->assign_by_ref("owner", $user);
	$GLOBALS['smarty']->assign_by_ref("foursquare_user", $foursquare_user);

	$more = array();

	$rsp = trips_calendars_get_for_user($user, $more);	
	$calendars = array();

	foreach ($rsp['rows'] as $row){
		trips_calendars_inflate_calendar($row);
		$calendars[] = $row;
	}

	$GLOBALS['smarty']->assign_by_ref("calendars", $calendars);

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$GLOBALS['smarty']->display("page_user_trips_calendars.txt");
	exit();

?>
