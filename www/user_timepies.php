<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("privatesquare_checkins_utils");
	loadlib("foursquare_users");
	loadlib("reverse_geoplanet");

	$fsq_id = get_int32("foursquare_id");

	if (! $fsq_id){
		error_404();
	}

	$url = "user/{$fsq_id}/timepies/";
	login_ensure_loggedin($url);

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

	$rsp = privatesquare_checkins_timesofday_for_user($owner, $more);
	$GLOBALS['smarty']->assign_by_ref("timesofday", $rsp['times']);

	$by_city = array();
	$by_venue = array();

	$localities = array();
	$localities_by_timeofday = array();
		
	foreach ($rsp['times'] as $timeofday => $ignore){

		$_rsp = privatesquare_checkins_localities_for_timeofday($owner, $timeofday);

		$by_city[$timeofday] = $_rsp['localities'];

		foreach ($_rsp['localities'] as $woeid => $ignore){

			if (! isset($localities[$woeid])){

				$loc = reverse_geoplanet_get_by_woeid($woeid, 'locality');
				$localities[$woeid] = $loc;
			}

			if (! is_array($localities_by_timeofday[$timeofday])){
				$localities_by_timeofday[$timeofday] = array();
			}

			$localities_by_timeofday[$timeofday][] = $localities[$woeid];
		}

		$_rsp = privatesquare_checkins_venues_for_timeofday($owner, $timeofday);
		$by_venue[$timeofday] = $_rsp['venues'];
	}

	$bboxes = array();

	foreach ($localities_by_timeofday as $timeofday => $_localities){

		# This is a dirty little hack that rest on the fact that
		# both checkins and localities have latitude/longitude keys

		$stats = privatesquare_checkins_utils_geo_stats($_localities);
		$bboxes[$timeofday] = $stats['bounding_box'];
	}

	$GLOBALS['smarty']->assign_by_ref("by_city", $by_city);
	$GLOBALS['smarty']->assign_by_ref("by_venue", $by_venue);
	$GLOBALS['smarty']->assign_by_ref("localities", $localities_by_timeofday);
	$GLOBALS['smarty']->assign_by_ref("bboxes", $bboxes);

	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$GLOBALS['smarty']->display("page_user_timepies.txt");
	exit();
?>
