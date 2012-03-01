<?php

	include("include/init.php");
	loadlib("privatesquare_checkins");
	loadlib("foursquare_users");

	$fsq_id = get_int32("foursquare_id");

	if (! $fsq_id){
		error_404();
	}

	$history_url = "user/{$fsq_id}/history/";
	login_ensure_loggedin($history_url);

	$fsq_user = foursquare_users_get_by_foursquare_id($fsq_id);

	if (! $fsq_user){
		error_404();
	}

	$owner = users_get_by_id($fsq_user['user_id']);
	$is_own = ($owner['id'] == $GLOBALS['cfg']['user']['id']) ? 1 : 0;

	# for now...

	if (! $is_own){
		error_403();
	}

	$lat = get_float('latitude');
	$lon = get_float('longitude');

	if (($lat) && ($lon)){

		$more = array();

		if (get_float('dist')) {
			$more['dist'] = get_float('dist');
		}
		if ( get_str('unit')) {
			$more['unit'] = get_str('unit');
		}

		$rsp = privatesquare_checkins_for_user_nearby($owner, $lat, $lon, $more);

		$GLOBALS['smarty']->assign_by_ref("owner", $owner);
		$GLOBALS['smarty']->assign_by_ref("is_own", $is_own);

		$GLOBALS['smarty']->assign_by_ref("venues", $rsp['rows']);

		$GLOBALS['smarty']->assign("latitude", $lat);
		$GLOBALS['smarty']->assign("longitude", $lon);
	}

	$GLOBALS['smarty']->display("page_user_history_nearby.txt");
	exit();
?>
