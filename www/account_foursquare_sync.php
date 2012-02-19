<?php

	include("include/init.php");

	login_ensure_loggedin("/account/foursquare/sync/");

	$crumb_key = "foursquare_sync";

	$GLOBALS['smarty']->assign("crumb_key", $crumb_key);

	# put this in a library? which one...

	$sync_states = array(
		0 => 'do not sync 4sq checkins',
		1 => 'only sync recent 4sq checkins',
		2 => 'sync all 4sq checkins past and future',
	);

	if ((post_isset("done")) && (crumb_check($crumb_key))){

		$ok = 1;

		if (! post_isset("sync")){
			$update_error = "missing sync";
			$ok = 0;
		}

		if ($ok){

			$sync = post_int32("sync");

			if (! isset($sync_states[$sync])){
				$update_error = "invalid sync";
				$ok = 0;
			}
		}

		if ($ok){

			if ($sync != $GLOBALS['cfg']['user']['sync_foursquare']){

				$update = array(
					'sync_foursquare' => $sync,
				);

				$ok = users_update_user($GLOBALS['cfg']['user'], $update);

				if ($ok){
					$GLOBALS['cfg']['user'] = users_get_by_id($GLOBALS['cfg']['user']['id']);
				}

				else {
					$update_error = "db error";
				}
			}

		}

		$GLOBALS['smarty']->assign("update", 1);
		$GLOBALS['smarty']->assign("update_ok", $ok);
		$GLOBALS['smarty']->assign("update_error", $update_error);
	}

	$GLOBALS['smarty']->assign_by_ref("sync_states", $sync_states);
	$GLOBALS['smarty']->display("page_account_foursquare_sync.txt");
	exit();

?>
