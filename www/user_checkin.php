<?php

	include("include/init.php");
	loadlib("privatesquare_checkins");
	loadlib("foursquare_users");

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

	# for now...

	if ($GLOBALS['cfg']['user']['id'] != $fsq_user['user_id']){
		error_403();
	}

	error_disabled();
?>
