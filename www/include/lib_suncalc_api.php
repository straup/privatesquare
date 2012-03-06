<?php

	# See also: https://github.com/RandomEtc/suncalc-api
	#
	# /times?lat=51.5&lon=-0.1
	# /position?date=2012-03-06T06:35:20.784Z&lat=51.5&lon=-0.1
	# (date is optional)

 	#################################################################

	loadlib("http");

 	#################################################################

	function suncalc_api_call($method, $args=array()){

		$req = array(array(
			$method,
			$args,
		));

		$rsp = suncalc_api_call_multi($req);

		if (! $rsp['ok']){
			return $rsp;
		}

		return $rsp['rows'][0];
	}

 	#################################################################

	function suncalc_api_call_multi($reqs){

		if (! $GLOBALS['cfg']['suncalc_api_endpoint']){
			return not_okay("suncalc api endpoint not defined");
		}

		$http_reqs = array();

		foreach ($reqs as $r){

			$method = $r[0];
			$args = $r[1];

			$url = _suncalc_request_url($method, $args);

			$http_reqs[] = array(
				'method' => 'GET',
				'url' => $url,
			);
		}

		$rsp_raw = http_multi($http_reqs);
		$rsp_parsed = array();

		$count_requests = count($rsp_raw);
		$count_errors = 0;

		for ($i=0; $i < $count_requests; $i++){

			$method = $reqs[$i][0];
			$raw = $rsp_raw[$i];

			$rsp = _suncalc_api_parse_response($raw, $method);
			$rsp['method'] = $method;

			$rsp_parsed[] = $rsp;

			if (! $rsp['ok']){
				$count_errors++;
			}
		}

		return okay(array(
			'count_requests' => $count_requests,
			'count_errors' => $count_errors,
			'rows' => $rsp_parsed
		));
	}

 	#################################################################

	function _suncalc_request_url($method, $args){

		$query = http_build_query($args);
		$url = $GLOBALS['cfg']['suncalc_api_endpoint'] . $method . "?" . $query;

		return $url;
	}

 	#################################################################

	function _suncalc_api_parse_response($rsp, $key='rsp'){

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){
			return not_okay("failed to parse response");
		}

		return okay(array(
			$key => $data
		));
	}

 	#################################################################
?>
