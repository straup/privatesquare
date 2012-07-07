<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("foursquare_users");

	$fsq_id = get_int32("foursquare_id");

	if (! $fsq_id){
		error_404();
	}

	login_ensure_loggedin();

	$fsq_user = foursquare_users_get_by_foursquare_id($fsq_id);

	if (! $fsq_user){
		error_404();
	}

	$owner = users_get_by_id($fsq_user['user_id']);
	$is_own = ($owner['id'] == $GLOBALS['cfg']['user']['id']) ? 1 : 0;

	if (! $is_own){
		error_403();
	}

	$status_map = privatesquare_checkins_status_map();
	$list_map = privatesquare_checkins_list_map();

	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);
	$GLOBALS['smarty']->assign_by_ref("list_map", $list_map);

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$GLOBALS['smarty']->display("page_user_atlas_of_desire.txt");
	exit();
?>
