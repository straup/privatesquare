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

	login_ensure_loggedin();

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

	$dow_map = array(
		0 => 'sunday',
		1 => 'monday',
		2 => 'tuesday',
		3 => 'wednesday',
		4 => 'thursday',
		5 => 'friday',
		6 => 'saturday',
	);

	$dow_map = array_flip($dow_map);

	$str_dow = get_str("dow");

	if (! isset($dow_map[$str_dow])){
		error_404();
	}

	$dow = $dow_map[$str_dow];

	$more = array(
		'dow' => $dow,
	);

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	if ($when = get_str("when")){
		$more['dow'] = $dow;
	}

	$more['inflate_locality'] = 1;

	$rsp = privatesquare_checkins_for_user($owner, $more);

	$status_map = privatesquare_checkins_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$enc_dow = urlencode(strtolower($str_dow));
	$pagination_url = "{$GLOBALS['cfg']['abs_root_url']}user/{$fsq_id}/history/{$enc_dow}/";
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);
	$GLOBALS['smarty']->assign_by_ref("is_own", $is_own);

	$export_formats = privatesquare_export_valid_formats();
	$GLOBALS['smarty']->assign("export_formats", array_keys($export_formats));

	$geo_stats = privatesquare_checkins_utils_geo_stats($rsp['rows']);
	$GLOBALS['smarty']->assign_by_ref("geo_stats", $geo_stats);

	$GLOBALS['smarty']->assign("dow", $str_dow);

	$GLOBALS['smarty']->assign_by_ref("checkins", $rsp['rows']);
	$GLOBALS['smarty']->display("page_user_history_dow.txt");
	exit();
?>
