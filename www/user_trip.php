<?php

	include("include/init.php");
	loadlib("trips");

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];

	if ($id = get_int32("trip_id")){
		$trip = trips_get_by_id($id);
	}

	elseif ($id = get_int32("dopplr_id")){
		$trip = trips_get_by_dopplr_id($id);
	}

	else {
		error_404();
	}

	if (! $trip){
		error_404();
	}

	if ($trip['user_id'] != $user['id']){
		error_403();
	}

	trips_inflate_trip($trip);

	# TO DO: other trips to this locality

	# TO DO: get checkins and atlas (want to go here) for locality

	$GLOBALS['smarty']->assign_by_ref("trip", $trip);

	$travel_map = trips_travel_type_map();
	$GLOBALS['smarty']->assign_by_ref("travel_map", $travel_map);

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	# $trip_crumb = crumb_generate("api", "privatesquare.trips.add");
	# $GLOBALS['smarty']->assign("trip_crumb", $trip_crumb);

	$GLOBALS['smarty']->display("page_user_trip.txt");
	exit();

?>
