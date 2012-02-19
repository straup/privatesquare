<?php

	include("include/init.php");
	loadlib("privatesquare_checkins");
	loadlib("foursquare_users");
	loadlib("foursquare_venues");

	$fsq_id = get_int32("foursquare_id");
	$chk_id = get_str("checkin_id");

	if ((! $fsq_id) || (! $chk_id)){
		error_404();
	}

	$history_url = "user/{$fsq_id}/history/";
	login_ensure_loggedin($history_url);

	$fsq_user = foursquare_users_get_by_foursquare_id($fsq_id);

	if (! $fsq_user){
		error_404();
	}

	$owner = users_get_by_id($fsq_user['user_id']);
	$is_own = ($GLOBALS['cfg']['user']['id'] == $owner['id']) ? 1 : 0;

	# for now...

	if (! $is_own){
		error_403();
	}

	$checkin = privatesquare_checkins_get_by_id($owner, $chk_id);

	if (! $checkin){
		error_404();
	}

	$checkin['venue'] = foursquare_venues_get_by_venue_id($checkin['venue_id']); 

	$status_map = privatesquare_checkins_status_map();

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);
	$GLOBALS['smarty']->assign_by_ref("checkin", $checkin);
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$GLOBALS['smarty']->assign("is_own", $is_own);

	$GLOBALS['smarty']->display("page_user_checkin.txt");
	exit();
?>
