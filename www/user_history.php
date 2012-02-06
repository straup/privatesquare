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

	# for now...

	if ($GLOBALS['cfg']['user']['id'] != $fsq_user['user_id']){
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

	$rsp = privatesquare_checkins_for_user($GLOBALS['cfg']['user'], $more);

	$GLOBALS['smarty']->assign("pagination_url", $GLOBALS['cfg']['abs_root_url'] . $history_url);

	$GLOBALS['smarty']->assign_by_ref("checkins", $rsp['rows']);
	$GLOBALS['smarty']->display("page_user_history.txt");
	exit();
?>
