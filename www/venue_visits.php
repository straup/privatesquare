<?php

	include("include/init.php");

	loadlib("foursquare_checkins");
	loadlib("privatesquare_checkins");
	loadlib("privatesquare_export");
	loadlib("reverse_geoplanet");

	login_ensure_loggedin();

	$owner = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$venue_id = get_str("venue_id");
	$provider = get_str("provider");

	if ($provider){

		$provider_id = venues_providers_label_to_id($provider);
		$venue = venues_get_by_venue_id_for_provider($venue_id, $provider_id);
	}

	else {
		$venue = venues_get_by_venue_id($venue_id);
	}

	if (! $venue){
		error_404();
	}

	$venue['data'] = json_decode($venue['data'], "as hash");
	$venue['locality'] = reverse_geoplanet_get_by_woeid($venue['locality'], 'locality');

	# TO DO: account for pagination and > n checkins

	$more = array(
		'venue_id' => $venue_id,
	);

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = privatesquare_checkins_for_user($owner, $more);
	$GLOBALS['smarty']->assign("checkins", $rsp['rows']);

	$status_map = privatesquare_checkins_status_map();
	$broadcast_map = foursquare_checkins_broadcast_map();

	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);
	$GLOBALS['smarty']->assign_by_ref("broadcast_map", $broadcast_map);

	$GLOBALS['smarty']->assign_by_ref("venue", $venue);

	$checkin_crumb = crumb_generate("api", "privatesquare.venues.checkin");
	$GLOBALS['smarty']->assign("checkin_crumb", $checkin_crumb);

	# did we arrive here from a checkin page?

	$success = get_str("success") ? 1 : 0;	
	$GLOBALS['smarty']->assign("success", $success);

	$GLOBALS['smarty']->assign("venue_id", $venue_id);
	
	$GLOBALS['smarty']->display("page_venue_visits.txt");
	exit();

?>
