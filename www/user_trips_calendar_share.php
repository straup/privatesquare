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

	trips_calendars_inflate_calendar($calendar);
	$GLOBALS['smarty']->assign_by_ref("calendar", $calendar);

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$edit_crumb = crumb_generate("api", "privatesquare.trips.calendars.editCalendar");
	$GLOBALS['smarty']->assign("edit_crumb", $edit_crumb);

	$delete_crumb = crumb_generate("api", "privatesquare.trips.calendars.deleteCalendar");
	$GLOBALS['smarty']->assign("delete_crumb", $delete_crumb);

	$GLOBALS['smarty']->display("page_user_trips_calendar_share.txt");
	exit();
?>
