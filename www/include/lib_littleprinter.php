<?php

	loadlib("bergcloud_users");
	loadlib("bergcloud_littleprinter");

	########################################################################

	function littleprinter_print_venue(&$venue, &$user){

		# TO DO: error handling...

		$venue['data'] = json_decode($venue['data'], 'as hash');

		# TO DO: lat,lon (and zoom) to tile

		$berg_user = bergcloud_users_get_by_user_id($user['id']);

		$GLOBALS['smarty']->assign_by_ref("venue", $venue);
		$msg = $GLOBALS['smarty']->fetch("page_littleprinter_venue.txt");

		$code = $berg_user['direct_print_code'];

		$rsp = bergcloud_littleprinter_direct_print($msg, $code);
		return $rsp;
	}

	########################################################################	
