<?php

	include("include/init.php");

	loadlib("foursquare_venues");
	loadlib("foursquare_checkins");
	loadlib("privatesquare_checkins");
	loadlib("privatesquare_export");

	login_ensure_loggedin($_SERVER['REQUEST_URI']);

	$owner = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$venue_id = get_str("venue_id");

	$venue = foursquare_venues_get_by_venue_id($venue_id);

	if (! $venue){
		error_404();
	}

	$venue['data'] = json_decode($venue['data'], "as hash");

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
