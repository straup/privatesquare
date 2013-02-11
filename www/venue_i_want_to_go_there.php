<?php

	include("include/init.php");

	login_ensure_loggedin();

	loadlib("foursquare_venues");

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

			}
		}

		# check if venue_id is a URL and strip

		$venue = foursquare_venues_get_by_venue_id($venue_id);

		if (! $venue){
			$rsp = foursquare_venues_archive_venue($venue_id);
			$venue = $rsp['venue'];
		}

		# check if not $venue

		$venue['data'] = json_decode($venue['data'], 'as hash');
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
