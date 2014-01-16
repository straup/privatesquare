<?php

	loadlib("foursquare_users");

 	#################################################################

	function urls_venue(&$venue){

		$provider = venues_providers_id_to_label($venue['provider_id']);
		$provider_id = $venue['provider_venue_id'];

		return $GLOBALS['cfg']['abs_root_url'] . "venue/{$provider}/{$provider_id}/";
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

	function urls_trip(&$trip){

		$user = users_get_by_id($trip['user_id']);
		$root = urls_user($user);

		$enc_trip = urlencode($trip['id']);
		return "{$root}trips/{$enc_trip}/";
	}

 	#################################################################

	function urls_user(&$user){

		$fsq_user = foursquare_users_get_by_user_id($user['id']);
		return $GLOBALS['cfg']['abs_root_url'] . "user/{$fsq_user['foursquare_id']}/";
	}

 	#################################################################

	# deprecated

	function urls_lists_for_user(&$user){
		return urls_atlas_of_desire_for_user($user);
	}

	function urls_atlas_of_desire_for_user($user){

		$fsq_user = foursquare_users_get_by_user_id($user['id']);

		return $GLOBALS['cfg']['abs_root_url'] . "user/{$fsq_user['foursquare_id']}/atlas/";
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
