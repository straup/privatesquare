<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("privatesquare_export");
	loadlib("foursquare_users");

	if (! $GLOBALS['cfg']['enable_feature_export']){
		error_disabled();
	}

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
	$export_func = "privatesquare_export_{$format}";

	loadlib($export_lib);

	$fetch_more = array();
		
	# No, you can't merge these yet. Maybe never.

	if ($when = get_str('when')){
		$fetch_more['when'] = $when;
	}

	else if ($venue_id = get_str('venue_id')){
		$fetch_more['venue_id'] = $venue_id;
	}

	# TO DO: something about nearby here...

	$rsp = privatesquare_checkins_export_for_user($owner, $fetch_more);
	$checkins = $rsp['rows'];

	#

	$fh = privatesquare_export_filehandle();

	$export_more = array();
		
	if (get_str('inline')){
		$export_more['inline'] = 1;
	}

	call_user_func($export_func, $fh, $checkins, $export_more);
	exit();

?>
