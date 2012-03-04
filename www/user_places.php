<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("privatesquare_checkins_utils");
	loadlib("foursquare_users");

	$fsq_id = get_int32("foursquare_id");

	if (! $fsq_id){
		error_404();
	}

	$history_url = "user/{$fsq_id}/places/";
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

	$rsp = privatesquare_checkins_localities_for_user($owner, $more);
	$GLOBALS['smarty']->assign_by_ref("places", $rsp['rows']);

	$geo_stats = privatesquare_checkins_utils_geo_stats($rsp['rows']);
	$GLOBALS['smarty']->assign_by_ref("geo_stats", $geo_stats);

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$pagination_url = urls_places_for_user($owner);
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);

	$GLOBALS['smarty']->display("page_user_places.txt");
	exit();
?>
