<?php

	include("include/init.php");
	loadlib("foursquare_venues");
	loadlib("privatesquare_checkins");

	login_ensure_loggedin($_SERVER['REQUEST_URI']);

	$venue_id = get_str("venue_id");

	$venue = foursquare_venues_get_by_venue_id($venue_id);

	if (! $venue){
		error_404();
	}

	$venue['data'] = json_decode($venue['data'], "as hash");

	$more = array(
		'venue_id' => $venue_id,
	);

	$checkins = privatesquare_checkins_for_user($GLOBALS['cfg']['user'], $more);
	$venue['checkins'] = $checkins['rows'];

	$status_map = privatesquare_checkins_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$GLOBALS['smarty']->assign_by_ref("venue", $venue);

	$GLOBALS['smarty']->display("page_venue.txt");
	exit();

?>
