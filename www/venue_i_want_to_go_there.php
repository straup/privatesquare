<?php

	include("include/init.php");

	login_ensure_loggedin();

	loadlib("foursquare_venues");

	if ($venue_id = request_str("venue_id")){

		$GLOBALS['smarty']->assign("step", "confirm");

		# check if venue_id is a URL and strip

		$venue = foursquare_venues_get_by_venue_id($venue_id);

		if (! $venue){
			$rsp = foursquare_venues_archive_venue($venue_id);
			$venue = $rsp['venue'];
		}

		# check if not $venue

		$GLOBALS['smarty']->assign_by_ref("venue", $venue);

		$update = post_isset("update");

		if ((0) && ($update)){

		}
	}

	else {
		# Search by name?

		$GLOBALS['smarty']->assign("step", "choose");
	}

	$GLOBALS['smarty']->display("page_venue_i_want_to_go_there.txt");
	exit();

?>
