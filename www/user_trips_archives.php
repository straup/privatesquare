<?php

	include("include/init.php");
	loadlib("trips");

	features_ensure_enabled("trips");
	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $user);

	$rsp = trips_stats_for_user($user);
	$stats = $rsp['stats'];

	$localities = array();

	foreach ($stats as $year => $details){
		foreach ($details['cities'] as $woeid => $ignore){

			if (isset($localities[$woeid])){
				continue;
			}

			$loc = whereonearth_fetch_woeid($woeid);
			$localities[$woeid] = $loc['data'];
		}
	}

	krsort($stats);

	$GLOBALS['smarty']->assign_by_ref("stats", $stats);
	$GLOBALS['smarty']->assign_by_ref("localities", $localities);

	$year = gmdate("Y", time());
	$GLOBALS['smarty']->assign("current_year", $year);

	$GLOBALS['smarty']->display("page_user_trips_archives.txt");
	exit();

?>
