<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("privatesquare_checkins_utils");
	loadlib("privatesquare_export");
	loadlib("foursquare_users");

	$fsq_id = get_int32("foursquare_id");

	if (! $fsq_id){
		error_404();
	}

	$status_id = get_int32("status_id");

	if (! $status_id){
		error_404();
	}

	$status_map = privatesquare_checkins_status_map();

	if (! isset($status_map[$status_id])){
		error_404();
	}

	# Dunno... this might change
	# (20120701/straup)

	if (in_array($status_id, array(0, 1))){
		error_404();
	}

	$str_status = $status_map[$status_id];

	$whereami = "user/{$fsq_id}/status/{$status_id}/";
	login_ensure_loggedin($whereami);

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

	$more = array(
		'status_id' => $status_id,
	);

	# TO DO: hooks for nearby or a WOE ID or nearby

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	# see notes in lib_privatesquare_checkins

	$rsp = privatesquare_checkins_venues_for_user($owner, $more);
	$GLOBALS['smarty']->assign_by_ref("venues", $rsp['rows']);
	
	$GLOBALS['smarty']->assign_by_ref("owner", $owner);
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);
	$GLOBALS['smarty']->assign("str_status", $str_status);

	$pagination_url = urls_places_for_user($owner) . "status/{$status_id}/";
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);

	$export_formats = privatesquare_export_valid_formats();
	$GLOBALS['smarty']->assign("export_formats", array_keys($export_formats));

	$GLOBALS['smarty']->display("page_user_status.txt");
	exit();
?>
