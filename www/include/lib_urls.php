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

	function urls_checkin(&$checkin){

		$user = users_get_by_id($checkin['user_id']);
		$fsq_user = foursquare_users_get_by_user_id($user['id']);

		return $GLOBALS['cfg']['abs_root_url'] . "user/{$fsq_user['foursquare_id']}/checkin/{$checkin['id']}/";
	}

 	#################################################################
?>
