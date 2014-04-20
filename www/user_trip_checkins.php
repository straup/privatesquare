<?php

	include("include/init.php");
	loadlib("trips");

	loadlib("privatesquare_checkins_utils");

	features_ensure_enabled("trips");

	login_ensure_loggedin();

	# in advance of a proper fix... (20140125/straup)
	$GLOBALS['cfg']['pagination_assign_smarty_variable'] = 0;

	$user = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $user);

	if ($id = get_int64("trip_id")){
		$trip = trips_get_by_id($user, $id);
	}

	elseif ($id = get_int32("dopplr_id")){
		$trip = trips_get_by_dopplr_id($user, $id);
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

 	$GLOBALS['smarty']->assign_by_ref("trip", $trip);

	$loc = $trip['locality'];
	$GLOBALS['smarty']->assign_by_ref("locality", $loc);

	$when = implode(";", array(
		$trip['arrival'],
		$trip['departure'],
	));

	$more = array(
		'locality' => $loc['woeid'],
		'when' => $when,
		'inflate_locality' => 1,
	);

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = privatesquare_checkins_for_user($user, $more);
	$GLOBALS['smarty']->assign_by_ref("checkins", $rsp['rows']);
	$GLOBALS['smarty']->assign_by_ref("pagination", $rsp['pagination']);

	$geo_stats = privatesquare_checkins_utils_geo_stats($rsp['rows']);
	$GLOBALS['smarty']->assign_by_ref("geo_stats", $geo_stats);

	$GLOBALS['smarty']->display("page_user_trip_checkins.txt");
	exit();

?>
