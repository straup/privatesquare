<?php

	loadlib("bergcloud_users");
	loadlib("bergcloud_littleprinter");

	########################################################################

	function littleprinter_print_venue(&$venue, &$user){

		$berg_user = bergcloud_users_get_by_user_id($user['id']);

		# TO DO: error handling...

		$venue['data'] = json_decode($venue['data'], 'as hash');
		$GLOBALS['smarty']->assign_by_ref("venue", $venue);

		$tile_url = littleprinter_tile_url($venue['latitude'], $venue['longitude']);
		$GLOBALS['smarty']->assign("tile_url", $tile_url);

		$msg = $GLOBALS['smarty']->fetch("page_littleprinter_venue.txt");

		$code = $berg_user['direct_print_code'];

		$rsp = bergcloud_littleprinter_direct_print($msg, $code);
		return $rsp;
	}

	########################################################################

	function littleprinter_tile_url($lat, $lon, $zoom=15){

		$provider = new MMaps_OpenStreetMap_Provider();

		$pt = new MMaps_Location($lat, $lon);

		$coord = $provider->locationCoordinate($pt);
		$coord = $coord->zoomTo($zoom);

		$x = intval($coord->column);
		$y = intval($coord->row);
		$z = intval($coord->zoom);

		return "http://tile.stamen.com/toner/{$z}/{$x}/{$y}.jpg";
	}

	########################################################################
