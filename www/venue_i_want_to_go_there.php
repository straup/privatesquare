<?php

	include("include/init.php");

	login_ensure_loggedin();

	loadlib("foursquare_venues");
	loadlib("privatesquare_checkins");

	if ($venue_id = request_str("venue_id")){

		$GLOBALS['smarty']->assign("step", "confirm");

		# Mostly because I am not sure what the check for when a 4sq URL
		# has a clean name in it, like this:
		# https://foursquare.com/v/yakitori-totto/454b1ad3f964a520a73c1fe3

		if (preg_match("/https?:\/\/foursquare.com\/v\/.*/", $venue_id)){

			if (preg_match("/\/([a-fA-F0-9]+)\/?$/", $venue_id, $m)){
				$venue_id = $m[1];
			}

			if (! $venue_id){
				# TO DO: error handling...
			}
		}

		$venue = foursquare_venues_get_by_venue_id($venue_id);

		if (! $venue){
			$rsp = foursquare_venues_archive_venue($venue_id);
			$venue = $rsp['venue'];
		}

		# TO DO: check if not $venue

		# TO DO: check to see if already been here...

		$venue['data'] = json_decode($venue['data'], 'as hash');
		$GLOBALS['smarty']->assign_by_ref("venue", $venue);

		$checkin_crumb = crumb_generate("api", "privatesquare.venues.checkin");
		$GLOBALS['smarty']->assign_by_ref("checkin_crumb", $checkin_crumb);

		$status_map = privatesquare_checkins_status_map("string keys");
		$GLOBALS['smarty']->assign("status_id", $status_map['i want to go there']);

		# TO DO: update if POST args (in a world... without javascript)
	}

	else {
		# Search by name?

		$GLOBALS['smarty']->assign("step", "choose");
	}

	$GLOBALS['smarty']->display("page_venue_i_want_to_go_there.txt");
	exit();

?>
