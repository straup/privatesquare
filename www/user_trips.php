<?php

	include("include/init.php");
	loadlib("trips");

	loadlib("privatesquare_checkins_utils");

	features_ensure_enabled("trips");
	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];
	$more = array();

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

	foreach ($rsp['rows'] as &$row){
		$loc = whereonearth_fetch_woeid($row['locality_id']);
		$row['latitude'] = $loc['data']['latitude'];
		$row['longitude'] = $loc['data']['longitude'];
	}

	$geo_stats = privatesquare_checkins_utils_geo_stats($rsp['rows']);
	$GLOBALS['smarty']->assign_by_ref("geo_stats", $geo_stats);

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);
	
	$GLOBALS['smarty']->display("page_user_trips.txt");
	exit();

?>
