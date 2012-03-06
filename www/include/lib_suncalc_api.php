<?php

	# See also:
	# https://gist.github.com/1984749

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

		# return _suncalc_api_parse_response($rsp);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){
			return not_okay("failed to parse response");
		}

		return okay(array(
			$method => $data
		));
	}

 	#################################################################

	function suncalc_api_call_multi($reqs){
		# write me...
	}

 	#################################################################

	function _suncalc_api_parse_response($rsp){
		# write me...
	}

 	#################################################################
?>
