<?php

	include("include/init.php");
	loadlib("trips");

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];

	if ($id = get_int32("id")){
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

	$trips_inflate_trip($trip);

	$GLOBALS['smarty']->assign_by_ref("trip", $trip);
	
	$GLOBALS['smarty']->display("page_user_trip.txt");
	exit();

?>
