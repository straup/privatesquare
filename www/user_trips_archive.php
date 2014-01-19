<?php

	include("include/init.php");
	loadlib("trips");

	features_ensure_enabled("trips");
	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];

	$year = get_int32("year");
	$month = get_int32("month");

	if (! $year){
		error_404();
	}

	if (! preg_match("/^\d{4}$/", $year)){
		error_404();
	}

	if (($month) && ($month < 1)){
		error_404();
	}

	if (($month) && ($month > 12)){
		error_404();
	}

	$more = array(
		'year' => $year,
		'month' => $month,
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

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	
	$GLOBALS['smarty']->assign("year", $year);
	$GLOBALS['smarty']->assign("month", $month);

	if ($month){
		$ts = strtotime("{$year}-{$month}");
		$GLOBALS['smarty']->assign("yearmonth_ts", $ts);
	}

	$pagination_url = urls_user($user) . "trips/archives/{$year}/";

	if ($month){
		$pagination_url .= "{$month}/";
	}

	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);

	$GLOBALS['smarty']->display("page_user_trips_archive.txt");
	exit();

?>
