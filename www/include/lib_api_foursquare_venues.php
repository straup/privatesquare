<?php

	loadlib("geo_utils");
	loadlib("foursquare_users");
	loadlib("foursquare_api");

 	#################################################################

	function api_foursquare_venues_search(){

		$lat = request_float('latitude');
		$lon = request_float('longitude');
		$alt = request_float('altitude');

		$query = request_str('query');

		# See this? It's a quick and dirty shim until I can figure
		# out how to pass 'sort' flags via the UI (20120201/straup)

		# $sort = request_float('sort');
		$sort = $GLOBALS['cfg']['foursquare_venues_sort'];

		$sort_func = "_api_foursquare_venues_sort_by_name";

		if ($sort == 'distance'){
			$sort_func = "_api_foursquare_venues_sort_by_distance";
		}

		if ((! $lat) || (! geo_utils_is_valid_latitude($lat))){
			api_output_error(999, "Missing or invalid latitude");
		}

		if ((! $lat) || (! geo_utils_is_valid_longitude($lon))){
			api_output_error(999, "Missing or invalid longitude");
		}

		$checkin_crumb = crumb_generate("api", "privatesquare.venues.checkin");
		$fsq_user = foursquare_users_get_by_user_id($GLOBALS['cfg']['user']['id']);

		$method = 'venues/search';

		if ($query){

			$args = array(
				'oauth_token' => $fsq_user['oauth_token'],
				'll' => "{$lat},{$lon}",
				'radius' => 1200,
				'limit' => 30,
				'intent' => 'match',
				'query' => $query
			);

			$rsp = foursquare_api_call($method, $args);

			if (! $rsp['ok']){
				_api_foursquare_error($rsp);
			}

			$venues = $rsp['rsp']['venues'];
			usort($venues, $sort_func);

			$out = array(
				'venues' => $venues,
				'query' => $query,
				'latitude' => $lat,
				'longitude' => $lon,
				'crumb' => $checkin_crumb,
			);

			api_output_ok($out);
		}

		$random_user = foursquare_users_random_user();

		if (! $random_user){
			$random_user = $fsq_user;
		}

		# https://developer.foursquare.com/docs/venues/search
		# TO DO: api_call_multi

		# first get stuff scoped to the current user

		$args = array(
			'oauth_token' => $fsq_user['oauth_token'],
			'll' => "{$lat},{$lon}",
			'limit' => 30,
			'intent' => 'checkin',
		);

		$rsp = foursquare_api_call($method, $args);

		if (! $rsp['ok']){
			_api_foursquare_error($rsp);
		}

		$venues = array();
		$seen = array();

		foreach ($rsp['rsp']['venues'] as $v){
			$venues[] = $v;
			$seen[] = $v['id'];			
		}

		# now just get whatever

		$args = array(
			'oauth_token' => $random_user['oauth_token'],
			'll' => "{$lat},{$lon}",
			'limit' => 30,
			'radius' => 800,
			'intent' => 'browse',
		);


		$rsp = foursquare_api_call($method, $args);

		if (! $rsp['ok']){
			_api_foursquare_error($rsp);
		}

		foreach ($rsp['rsp']['venues'] as $v){

			if (! in_array($v['id'], $seen)){
				$venues[] = $v;
			}
		}

		usort($venues, $sort_func);

		# go!

		$out = array(
			'venues' => $venues,
			'latitude' => $lat,
			'longitude' => $lon,
			'crumb' => $checkin_crumb,
		);

		api_output_ok($out);
	}

 	#################################################################

	function _api_foursquare_venues_sort_by_name($a, $b){
		return strcmp(strtoupper($a["name"]), strtoupper($b["name"]));
	}

	function _api_foursquare_venues_sort_by_distance($a, $b){
		return $a['location']['distance'] - $b['location']['distance'];
	}

 	#################################################################

	function _api_foursquare_error(&$rsp){

		$error = json_decode($rsp['body'], 'as hash');
		$meta = $error['meta'];

		$msg = "{$meta['code']}: {$meta['errorType']},	{$meta['errorDetail']}";
		api_output_error(999, $msg);
	}

 	#################################################################
?>
