<?php

	include("include/init.php");

	loadlib("foursquare_venues");
	loadlib("foursquare_checkins");
	loadlib("privatesquare_checkins");
	loadlib("privatesquare_export");
	loadlib("reverse_geoplanet");

	login_ensure_loggedin($_SERVER['REQUEST_URI']);

	$owner = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$venue_id = get_str("venue_id");

	$venue = foursquare_venues_get_by_venue_id($venue_id);

	if (! $venue){
		error_404();
	}

	$venue['data'] = json_decode($venue['data'], "as hash");
	$venue['locality'] = reverse_geoplanet_get_by_woeid($venue['locality'], 'locality');

	# TO DO: account for pagination and > n checkins

	$more = array(
		'venue_id' => $venue_id,
	);

	$checkins = privatesquare_checkins_for_user($owner, $more);
	$venue['checkins'] = $checkins['rows'];

	$status_map = privatesquare_checkins_status_map();
	$broadcast_map = foursquare_checkins_broadcast_map();

	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);
	$GLOBALS['smarty']->assign_by_ref("broadcast_map", $broadcast_map);

	$statuses = privatesquare_checkins_statuses_for_user($owner, $more);

	if (count(array_keys($statuses)) > 1){
		$has_visited = 1;
	}

	else if (isset($statuses['2'])){
		$has_visited = 0;
	}

	else if (count(array_keys($statuses))){
		$has_visited = 1;
	}

	$GLOBALS['smarty']->assign_by_ref("statuses", $statuses);
	$GLOBALS['smarty']->assign_by_ref("has_visited", $has_visited);

	$GLOBALS['smarty']->assign_by_ref("venue", $venue);

	$checkin_crumb = crumb_generate("api", "privatesquare.venues.checkin");
	$GLOBALS['smarty']->assign("checkin_crumb", $checkin_crumb);

	# did we arrive here from a checkin page?

	$success = get_str("success") ? 1 : 0;	
	$GLOBALS['smarty']->assign("success", $success);

	$GLOBALS['smarty']->assign("venue_id", $venue_id);

	$export_formats = privatesquare_export_valid_formats();
	$GLOBALS['smarty']->assign("export_formats", array_keys($export_formats));
	
	$GLOBALS['smarty']->display("page_venue.txt");
	exit();

?>
