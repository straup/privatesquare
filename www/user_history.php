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

	#

	if ($deleted = get_str("deleted")){

		$GLOBALS['smarty']->assign("deleted_checkin", 1);

		if ($venue_id = get_str("venue_id")){
			if ($venue = foursquare_venues_get_by_venue_id($venue_id)){
				$GLOBALS['smarty']->assign_by_ref("deleted_checkin_venue", $venue);
			}
		}

		if ($foursquare_checkin = get_str("foursquare_checkin")){
			$mock_checkin = array(
				"checkin_id" => $foursquare_checkin,
				"user_id" => $owner['id'],
			);

			$GLOBALS['smarty']->assign_by_ref("mock_checkin", $mock_checkin);
		}
	}

	#

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

	$more['inflate_locality'] = 1;

	$rsp = privatesquare_checkins_for_user($owner, $more);

	# TO DO: oh god...timezones :-(

	if ($when){
		list($start, $stop) = datetime_when_parse($more['when']);

		$GLOBALS['smarty']->assign("when", $when);
		$GLOBALS['smarty']->assign("start", $start);
		$GLOBALS['smarty']->assign("stop", $stop);

		$bookends = privatesquare_checkins_bookends_for_date($owner, $when);
		$GLOBALS['smarty']->assign("bookends", $bookends);
	}

	$status_map = privatesquare_checkins_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$GLOBALS['smarty']->assign("pagination_url", $GLOBALS['cfg']['abs_root_url'] . $history_url);

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);
	$GLOBALS['smarty']->assign_by_ref("is_own", $is_own);

	$export_formats = privatesquare_export_valid_formats();
	$GLOBALS['smarty']->assign("export_formats", array_keys($export_formats));

	$geo_stats = privatesquare_checkins_utils_geo_stats($rsp['rows']);
	$GLOBALS['smarty']->assign_by_ref("geo_stats", $geo_stats);

	$GLOBALS['smarty']->assign_by_ref("checkins", $rsp['rows']);
	$GLOBALS['smarty']->display("page_user_history.txt");
	exit();
?>
