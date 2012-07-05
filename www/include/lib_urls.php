<?php

	loadlib("foursquare_users");

 	#################################################################

	function urls_venue(&$venue){

		return $GLOBALS['cfg']['abs_root_url'] . "venue/{$venue['venue_id']}/";
	}

 	#################################################################

	function urls_history_for_user(&$user){

		$fsq_user = foursquare_users_get_by_user_id($user['id']);

		return $GLOBALS['cfg']['abs_root_url'] . "user/{$fsq_user['foursquare_id']}/history/";
	}

 	#################################################################

	function urls_places_for_user(&$user){

		$fsq_user = foursquare_users_get_by_user_id($user['id']);

		return $GLOBALS['cfg']['abs_root_url'] . "user/{$fsq_user['foursquare_id']}/places/";
	}

 	#################################################################

	# IT'S CALLED 'foo' BECAUSE IT NEEDS A PROPER NAME
	# (20120704/straup)

	function urls_foo_for_user(&$user){

		$fsq_user = foursquare_users_get_by_user_id($user['id']);

		return $GLOBALS['cfg']['abs_root_url'] . "user/{$fsq_user['foursquare_id']}/status/";
	}

 	#################################################################

	function urls_lists_for_user(&$user){

		$fsq_user = foursquare_users_get_by_user_id($user['id']);

		return $GLOBALS['cfg']['abs_root_url'] . "user/{$fsq_user['foursquare_id']}/lists/";
	}

 	#################################################################

	function urls_nearby_for_user(&$user){

		$history = urls_history_for_user($user);
		return $history . "nearby/";
	}

 	#################################################################

	function urls_checkin(&$checkin){

		$user = users_get_by_id($checkin['user_id']);
		$fsq_user = foursquare_users_get_by_user_id($user['id']);

		return $GLOBALS['cfg']['abs_root_url'] . "user/{$fsq_user['foursquare_id']}/checkin/{$checkin['id']}/";
	}

 	#################################################################
?>
