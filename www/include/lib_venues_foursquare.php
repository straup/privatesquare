<?php

	#################################################################

	function venues_foursquare_fetch_venue($venue_id){

		loadlib("users");
		loadlib("api");

		$fsq_user = users_random_user();

		$method = "venues/{$venue_id}";

		$args = array(
			'oauth_token' => $fsq_user['oauth_token'],
		);

		$rsp = api_call($method, $args);
		return $rsp;
	}

	#################################################################

	# the end
