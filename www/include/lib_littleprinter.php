<?php

	loadlib("bergcloud_users");
	loadlib("bergcloud_littleprinter");
	loadlib("littleprinter_maps");

	########################################################################

	function littleprinter_print_venue(&$venue, &$berg_user){

		$venue['data'] = json_decode($venue['data'], 'as hash');
		$GLOBALS['smarty']->assign_by_ref("venue", $venue);

		$tile_url = littleprinter_maps_tile_url($venue['latitude'], $venue['longitude']);
		$GLOBALS['smarty']->assign("tile_url", $tile_url);

		$msg = $GLOBALS['smarty']->fetch("page_littleprinter_venue.txt");

		$code = $berg_user['direct_print_code'];

		$rsp = bergcloud_littleprinter_direct_print($msg, $code);
		return $rsp;
	}

	########################################################################
