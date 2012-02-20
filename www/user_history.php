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

	$more = array();

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	if ($when = get_str("when")){
		$more['when'] = $when;
		$history_url .= urlencode($when) . "/";

		# TO DO: find some better heuristic for this number
		# besides "pull it out of my ass" (20120206/straup)
		$more['per_page'] = 100;
	}

	$rsp = privatesquare_checkins_for_user($owner, $more);

	# TO DO: oh god...timezones :-(

	if ($when){
		list($start, $stop) = datetime_when_parse($more['when']);

		$GLOBALS['smarty']->assign("when", $when);
		$GLOBALS['smarty']->assign("start", $start);
		$GLOBALS['smarty']->assign("stop", $stop);
	}

	$status_map = privatesquare_checkins_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$GLOBALS['smarty']->assign("pagination_url", $GLOBALS['cfg']['abs_root_url'] . $history_url);

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);
	$GLOBALS['smarty']->assign_by_ref("is_own", $is_own);

	$GLOBALS['smarty']->assign_by_ref("checkins", $rsp['rows']);
	$GLOBALS['smarty']->display("page_user_history.txt");
	exit();
?>
