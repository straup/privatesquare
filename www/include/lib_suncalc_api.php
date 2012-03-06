<?php

	# /times?lat=51.5&lon=-0.1
	# /position?lat=51.5&lon=-0.1
	# /position?date=2012-03-06T06:35:20.784Z&lat=51.5&lon=-0.1

 	#################################################################

	loadlib("http");

 	#################################################################

	function suncalc_api_call($method, $args=array()){

		if (! $GLOBALS['cfg']['suncalc_api_endpoint']){
			return not_okay("suncalc api endpoint not defined");
		}

		$query = http_build_query($args);

		$url = $GLOBALS['cfg']['suncalc_api_endpoint'] . $method . "?" . $query;
		$rsp = http_get($url);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){
			return not_okay("failed to parse response");
		}

		return okay(array(
			'data' => $data
		));
	}

 	#################################################################
