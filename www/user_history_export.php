<?php

	include("include/init.php");

	loadlib("privatesquare_checkins_export");
	loadlib("privatesquare_export");
	loadlib("foursquare_users");

	features_ensure_enabled("export");

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
	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$is_own = ($owner['id'] == $GLOBALS['cfg']['user']['id']) ? 1 : 0;

	if (! $is_own){
		error_403();
	}

	$format = get_str("format");

	if (! privatesquare_export_is_valid_format($format)){

		$map = privatesquare_export_valid_formats();

		$GLOBALS['smarty']->assign("valid_formats", array_keys($map));
		$GLOBALS['smarty']->display("page_user_history_export.txt");
		exit();
	}

	$export_lib = "privatesquare_export_{$format}";
	$export_func = "privatesquare_export_{$format}_row";

	loadlib($export_lib);

	$fetch_what = array(
		'user_id' => $owner['id'],
	);
		
	if ($when = get_str('when')){
		$fetch_what['when'] = $when;
	}

	else if ($venue_id = get_str('venue_id')){
		$fetch_what['venue_id'] = $venue_id;
	}

	else if ($locality = get_str('locality')){
		$fetch_what['locality'] = $locality;
	}

	$fh = fopen("php://output", "w");

	$map = privatesquare_export_valid_formats();
	$type = $map[$format];

	$headers = array(
		'Content-type' => $type,
	);

	$more = array();

	$more['inline'] = (get_isset("inline")) ? 1 : 0;

	privatesquare_export_send_headers($headers, $more);

	privatesquare_checkins_export($fetch_what, $export_func, $fh);
	exit();

?>
