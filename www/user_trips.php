<?php

	include("include/init.php");
	loadlib("trips");

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
	
	$GLOBALS['smarty']->display("page_user_trips.txt");
	exit();

?>
