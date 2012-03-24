<?php

	include("include/init.php");

	loadlib("suncalc_simple");
	loadlib("suncalc_api");
	loadlib("suncalc");

	$now = time();
	$date = date('c', $now);

	$lat = 37.756532;
	$lon = -122.422149;

	$times = suncalc_api_call('times', array('date'=>$date, 'lat' => $lat, 'lon' => $lon));
	dumper($times);

	$times = suncalc_get_times($date, $lat, $lon);
	dumper($times);

	$pos = suncalc_get_position($date, $lat, $lon);
	dumper($pos);

	$rsp = suncalc_simple($date, $lat, $lon);
	dumper($rsp);

	exit();

	loadlib("geo_cities_yql");
	loadlib("foursquare_api");
	loadlib("foursquare_users");
	loadlib("privatesquare_checkins");
	loadlib("weather_google");

	$fsq_user = foursquare_users_get_by_user_id($GLOBALS['cfg']['user']['id']);

		$args = array(
			'oauth_token' => $fsq_user['oauth_token'],
		);

		$rsp = foursquare_api_call('users/self', $args);

		$foursquare_id = $rsp['rsp']['user']['id'];
		$username = $rsp['rsp']['user']['firstName'];
		$email = $rsp['rsp']['user']['contact']['email'];

		if (! $email){
			$email = "{$foursquare_id}@donotsend-foursquare.com";
		}

		if (isset($rsp['rsp']['user']['lastName'])){
			$username .= " {$rsp['rsp']['user']['lastName']}";
		}

		dumper($username);
		exit;

	$rsp = weather_google_conditions(37.756553, -122.422162);

	dumper($rsp);
	exit;
	
	$rsp = geo_cities_yql_geocode_city("Montreal");
	$yul = $rsp[0];

	$fsq_user = foursquare_users_random_user();

	$method = 'venues/search';

	$args = array(
		'oauth_token' => $fsq_user['oauth_token'],
		'll' => "{$yul['latitude']},{$yul['longitude']}",
		'limit' => 30,
		'query' => "poutine"
	);

	$rsp = foursquare_api_call($method, $args);
	dumper($rsp);

	exit();
?>
