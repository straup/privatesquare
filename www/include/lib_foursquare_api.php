<?php

	loadlib("http");

	#################################################################

	$GLOBALS['foursquare_api_endpoint'] = 'https://api.foursquare.com/v2/';
	$GLOBALS['foursquare_oauth_endpoint'] = 'https://foursquare.com/oauth2/';

	#################################################################

	function foursquare_api_call($method, $args=array(), $more=array()){

		$method = ltrim($method, "/");
		$args['v'] = gmdate("Ymd", time());

		if ($more['method'] == 'POST'){

			$url = $GLOBALS['foursquare_api_endpoint'] . $method;
			$rsp = http_post($url, $args);
		}

		else{
			$query = http_build_query($args);
			$url = $GLOBALS['foursquare_api_endpoint'] . $method . "?{$query}";
			$rsp = http_get($url);
		}

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], "as hash");

		if (! $data){
			return not_okay("failed to parse response");
		}

		return okay(array(
			"rsp" => $data['response'],
		));
	}

	#################################################################

	function foursquare_api_get_auth_url(){

		$callback = $GLOBALS['cfg']['abs_root_url'] . $GLOBALS['cfg']['foursquare_oauth_callback'];

		$oauth_key = $GLOBALS['cfg']['foursquare_oauth_key'];
        	$oauth_redir = urlencode($callback);

		$url = "{$GLOBALS['foursquare_oauth_endpoint']}authenticate?client_id={$oauth_key}&response_type=code&redirect_uri=$oauth_redir";
		return $url;
	}

	#################################################################

	function foursquare_api_get_auth_token($code){

		$callback = $GLOBALS['cfg']['abs_root_url'] . $GLOBALS['cfg']['foursquare_oauth_callback'];

		$args = array(
			'client_id' => $GLOBALS['cfg']['foursquare_oauth_key'],
			'client_secret' => $GLOBALS['cfg']['foursquare_oauth_secret'],
			'grant_type' => 'authorization_code',
			'redirect_uri' => $callback,
			'code' => $code,
		);

		$query = http_build_query($args);

		$url = "{$GLOBALS['foursquare_oauth_endpoint']}access_token?{$query}";
		$rsp = http_get($url);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if ((! $data) || (! $data['access_token'])){
			return not_okay("failed to parse response");
		}

		return okay(array(
			'oauth_token' => $data['access_token']
		));
	}

	#################################################################

?>
