<?php

	include("include/init.php");
	loadlib("trips");

	features_ensure_enabled("trips");
	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $user);

	$year = get_int32("year");
	$woeid = get_int32("woeid");

	if (! $year){
		error_404();
	}

	if (! $woeid){
		error_404();
	}

	$rsp = whereonearth_fetch_woeid($woeid);

	if (! $rsp['ok']){
		error_404();
	}

	$locality = $rsp['data'];
	$GLOBALS['smarty']->assign_by_ref("locality", $locality);

	$more = array(
		'year' => $year,
		'where' => 'locality',
		'woeid' => $woeid,
		'per_page' => 40
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
	
	$GLOBALS['smarty']->assign("year", $year);

	$pagination_url = urls_user($user) . "trips/archives/{$year}/places/{$locality['woeid']}";
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);

	$GLOBALS['smarty']->display("page_user_trips_archive_place.txt");
	exit();

?>
