<?php

	loadlib("http");

	$GLOBALS['youarehere_host'] = '';
	$GLOBALS['youarehere_api_endpoint'] = 'api/rest/';
	$GLOBALS['youarehere_auth_endpoint'] = '';

	########################################################################

	function youarehere_api_auth_user_url(){

		$args = array(
			'scope' => '',
			'api_key' => '',
			'redirect_uri' => $GLOBALS['cfg']['abs_root_url'] . 'youarehere/auth/',
		);

		$query = http_build_query($args);

		$url = $GLOBALS['youarehere_host'] . $GLOBALS['youarehere_auth_endpoint'] . '?'. $query;
		return $url;
	}

	########################################################################

	function youarehere_api_call($method, $args=array(), $more=array()){

		$args['method'] = $method;

		$url = $GLOBALS['youarehere_host'] . $GLOBALS['youarehere_api_endpoint'];

		$rsp = http_post($url, $args);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){
			$rsp['ok'] = 0;
			$rsp['error'] = 'Failed to parse JSON';
			return $rsp;
		}

		$rsp['data'] = $data;
		return $rsp;
	}

	########################################################################

	# the end
