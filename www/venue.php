<?php

	include("include/init.php");
	loadlib("foursquare_venues");

	login_ensure_loggedin($_SERVER['REQUEST_URI']);

	$venue_id = get_str("venue_id");

	$venue = foursquare_venues_get_by_venue_id($venue_id);

	if (! $venue){
		error_404();
	}

	$venue['data'] = json_decode($venue['data'], "as hash");

	# TO DO: get user history for this place...

	$GLOBALS['smarty']->assign_by_ref("venue", $venue);

	$GLOBALS['smarty']->display("page_venue.txt");
	exit();

?>
