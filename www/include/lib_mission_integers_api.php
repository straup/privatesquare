<?php

	loadlib("http");

	$GLOBALS['mission_integers_api_endpoint'] = 'http://www.missionintegers.com/';

	########################################################################

	function mission_integers_api_call($method, $args=array()){

		$args['format'] = 'json';

		$headers = array();
		$more = array('http_timeout' => 10);

		$url = $GLOBALS['mission_integers_api_endpoint'] . urlencode($method);
		$rsp = http_post($url, $args, $headers, $more);

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
