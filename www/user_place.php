<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("privatesquare_export");
	loadlib("foursquare_users");

	$fsq_id = get_int32("foursquare_id");

	if (! $fsq_id){
		error_404();
	}

	$woeid = get_int32("woeid");

	if (! $woeid){
		error_404();
	}

	$history_url = "user/{$fsq_id}/places/{$woeid}/";
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

	$more = array(
		'locality' => $woeid,
	);

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = privatesquare_checkins_venues_for_user($owner, $more);
	$GLOBALS['smarty']->assign_by_ref("venues", $rsp['rows']);

	$locality = reverse_geoplanet_get_by_woeid($woeid, 'locality');
	$GLOBALS['smarty']->assign_by_ref("locality", $locality);

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$pagination_url = urls_places_for_user($owner) . "{$woeid}/";
	$GLOBALS['smarty']->assign("pagination_url", $pagination_url);

	$export_formats = privatesquare_export_valid_formats();
	$GLOBALS['smarty']->assign("export_formats", array_keys($export_formats));

	$GLOBALS['smarty']->display("page_user_place.txt");
	exit();
?>
