<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("privatesquare_checkins_utils");
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

	# TO DO: check whether any of these are 'iwanttogothere' ... 

	if (($lat) && ($lon)){

		$more = array(
			'dist' => 0.5,
		);

		if ($d = get_float('dist')) {
			$more['dist'] = $d;
		}

		if ( get_str('unit')) {
			$more['unit'] = get_str('unit');
		}

		$rsp = privatesquare_checkins_for_user_nearby($owner, $lat, $lon, $more);

		$geo_stats = privatesquare_checkins_utils_geo_stats($rsp['rows']);
		$GLOBALS['smarty']->assign_by_ref("geo_stats", $geo_stats);

		$GLOBALS['smarty']->assign_by_ref("owner", $owner);
		$GLOBALS['smarty']->assign_by_ref("is_own", $is_own);

		$GLOBALS['smarty']->assign_by_ref("venues", $rsp['rows']);

		$GLOBALS['smarty']->assign("latitude", $lat);
		$GLOBALS['smarty']->assign("longitude", $lon);

		$nearby_bbox = geo_utils_bbox_from_point($lat, $lon, ($more['dist'] * 1.25), 'm');
		$GLOBALS['smarty']->assign("nearby_bbox", $nearby_bbox);
	}

	$GLOBALS['smarty']->display("page_user_history_nearby.txt");
	exit();
?>
