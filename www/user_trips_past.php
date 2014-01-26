<?php

	include("include/init.php");
	loadlib("trips");

	features_ensure_enabled("trips");
	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];

	$more = array(
		'when' => 'past'
	);

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = trips_get_for_user($user, $more);
	$trips = array();

	foreach ($rsp['rows'] as $row){
		trips_inflate_trip($row);
		$trips[] = $row;
	}

	$GLOBALS['smarty']->assign_by_ref("trips", $trips);

	$geo_stats = privatesquare_checkins_utils_geo_stats($trips);
	$GLOBALS['smarty']->assign_by_ref("geo_stats", $geo_stats);

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$pagination_url = urls_user($user) . "trips/past/";
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);
	
	$GLOBALS['smarty']->display("page_user_trips_past.txt");
	exit();

?>
