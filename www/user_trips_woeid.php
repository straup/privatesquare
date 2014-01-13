<?php

	include("include/init.php");
	loadlib("trips");

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];

	$woeid = get_int32("woeid");

	if (! $woeid){
		error_404();
	}

	$loc = geo_flickr_get_woeid($woeid);

	$placetypes = array(
		'locality', 'region', 'country'
	);

	# maybe something more useful than a 404?

	if (! in_array($loc['placetype'], $placetypes)){
		error_404();
	}

	$more = array();

	$more[$loc['placetype_id']] = $woeid;

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
