<?php

	include("include/init.php");

	if (! $GLOBALS['cfg']['user']['id']){

		$GLOBALS['smarty']->display("page_index_loggedout.txt");
		exit();
	}

	loadlib("privatesquare_checkins");
	loadlib("foursquare_checkins");

	$status_map = privatesquare_checkins_status_map();
	$broadcast_map = foursquare_checkins_broadcast_map();

	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);
	$GLOBALS['smarty']->assign_by_ref("broadcast_map", $broadcast_map);

	$GLOBALS['smarty']->assign("appcache", "checkin");

	$GLOBALS['smarty']->display("page_index.txt");
	exit();

?>
