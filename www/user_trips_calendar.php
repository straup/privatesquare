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

	$id = get_int64("id");
	$calendar = trips_calendars_get_by_id($id);

	if (! $calendar){
		error_404();
	}

	if ($calendar['user_id'] != $user['id']){
		error_404();
	}

	$GLOBALS['smarty']->assign_by_ref("calendar", $calendar);

	$GLOBALS['smarty']->display("page_user_trips_calendar.txt");
	exit();
?>
