<?php

	loadlib("http");

	$GLOBALS['london_integers_api_endpoint'] = 'http://api.londonintegers.com/';

	########################################################################

	function london_integers_api_call($method, $args=array(), $more=array()){

		$defaults = array(
			'request_method' => 'GET',
		);

		$more = array_merge($defaults, $more);

		$request_method = strtoupper($more['request_method']);

		if ($request_method == 'GET'){

			$url = $GLOBALS['london_integers_api_endpoint'] . $method;

			if (count($args)){
				$args = http_build_query($args);
				$url .= "?" . $args;
			}

			$rsp = http_get($url);
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
