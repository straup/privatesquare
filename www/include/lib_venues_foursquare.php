<?php

	#################################################################

	function venues_foursquare_fetch_venue($venue_id){

		loadlib("foursquare_users");
		loadlib("foursquare_api");

		$fsq_user = foursquare_users_random_user();

		$method = "venues/{$venue_id}";

		$args = array(
			'oauth_token' => $fsq_user['oauth_token'],
		);

		$rsp = foursquare_api_call($method, $args);
		return $rsp;
	}

	#################################################################

	# the end
