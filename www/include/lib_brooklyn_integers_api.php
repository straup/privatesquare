<?php

	loadlib("http");

	$GLOBALS['brooklyn_integers_api_endpoint'] = 'http://api.brooklynintegers.com/rest/';

	########################################################################

	# this is just syntactic sugar

	function brooklyn_integers_api_post($method, $args=array()){

		$more = array('request_method' => 'POST');
		return brooklyn_integers_api_call($method, $args, $more);
	}

	########################################################################

	function brooklyn_integers_api_call($method, $args=array(), $more=array()){

		$defaults = array(
			'request_method' => 'GET',
		);

		$more = array_merge($defaults, $more);

		$args['method'] = $method;
		$args['format'] = 'json';

		$request_method = strtoupper($more['request_method']);

		if ($request_method == 'GET'){

			$args = http_build_query($args);
			$url = $GLOBALS['brooklyn_integers_api_endpoint'] . "?" . $args;
			$rsp = http_get($url);
		}

		else if ($request_method == 'POST'){

			$url = $GLOBALS['brooklyn_integers_api_endpoint'];
			$rsp = http_post($url, $args);
		}

		else {

			return array(
				'ok' => 0,
				'error' => 'Not a valid request method',
			);
		}

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){

			return array(
				'ok' => 0,
				'error' => 'Failed to parse response',
			);
		}

		return array(
			'ok' => 1,
			'response' => $data,
		);
	}

	########################################################################

?>
