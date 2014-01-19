<?php

	include("include/init.php");
	loadlib("trips");
	loadlib("whereonearth");

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];

	$more = array();

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = trips_get_places_for_user($user, $more);
	$places = array();

	foreach ($rsp['rows'] as $row){
		$rsp2 = whereonearth_fetch_woeid($row['locality_id']);
		$row['locality'] = $rsp2['data'];

		# TO DO: get more stats (20140118/straup)
		$places[] = $row;
	}

	$GLOBALS['smarty']->assign_by_ref("places", $places);

	$pagination_url = urls_user($user) . "trips/places/";
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);
	
	$GLOBALS['smarty']->display("page_user_trips_places.txt");
	exit();

?>
