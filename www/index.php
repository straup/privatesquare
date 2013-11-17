<?php

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("foursquare_checkins");

	if (! $GLOBALS['cfg']['user']['id']){

		$GLOBALS['smarty']->display("page_index_loggedout.txt");
		exit();
	}

	if ($provider = get_str("provider")){

		$provider_id = venues_providers_label_to_id($provider);

		if (! venues_providers_is_valid_provider($provider_id)){

			header("location: {$GLOBALS['cfg']['abs_root_url']}");
			exit();
		}

		$GLOBALS['smarty']->assign("provider", $provider);
	}

	$status_map = privatesquare_checkins_status_map();
	$broadcast_map = foursquare_checkins_broadcast_map();

	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);
	$GLOBALS['smarty']->assign_by_ref("broadcast_map", $broadcast_map);

	$GLOBALS['smarty']->assign("appcache", "checkin");

	$GLOBALS['smarty']->display("page_index.txt");
	exit();

?>
