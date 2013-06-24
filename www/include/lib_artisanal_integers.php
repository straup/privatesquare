<?php

	#################################################################

	$GLOBALS['artisanal_integers_providers'] = array(
		'brooklyn',
		'london',
		'mission',
	);

	#################################################################

	function artisanal_integers_available_providers(){
		return $GLOBALS['artisanal_integers_providers'];
	}

	#################################################################

	function artisanal_integers_is_valid_provider($provider){

		$available = artisanal_integers_available_providers();

		if (! in_array($provider, $available)){
			return 0;
		}

		return 1;
	}

	#################################################################

	function artisanal_integers_create($provider=null){

		if (! features_is_enabled("artisanal_integers")){
			return array("ok" => 0, "error" => "artisanal integers are currently disabled", "error_code" => -1);
		}

		if ($provider){

			if (! artisanal_integers_is_valid_provider($provider)){
				return array("ok" => 0, "error" => "invalid provider");
			}
		}

		else {

			$count = count($GLOBALS['artisanal_integers_providers']);

			if (! $count){
				return array("ok" => 0, "error" => "no providers defined");
			}

			$idx = rand(1, $count) - 1;
			$provider = $GLOBALS['artisanal_integers_providers'][$idx];
		}

		$func_name = "artisanal_integers_create_{$provider}_integer";

		if (! function_exists($func_name)){
			return array("ok" => 0, "error" => "no handler defined for {$provider}");
		}

		$rsp = call_user_func($func_name);

		if (! $rsp['ok']){
			return $rsp;
		}

		$rsp['provider'] = $provider;
		return $rsp;
	}

	#################################################################

	function artisanal_integers_create_brooklyn_integer(){

		loadlib("brooklyn_integers_api");

		$method = "brooklyn.integers.create";
		$rsp = brooklyn_integers_api_post($method);

		if (! $rsp['ok']){
			return $rsp;
		}

		return array('ok' => 1, 'integer' => $rsp['response']['integer']);
	}

	#################################################################

	function artisanal_integers_create_mission_integer(){

		loadlib("mission_integers_api");

		$method = 'next-int';
		$rsp = mission_integers_api_call($method);

		if (! $rsp['ok']){
			return $rsp;
		}

		return array('ok' => 1, 'integer' => $rsp['response']['integer']);
	}

	#################################################################

	function artisanal_integers_create_london_integer(){

		loadlib("london_integers_api");

		$method = 'london.integers.create';
		$rsp = london_integers_api_call($method);

		if (! $rsp['ok']){
			return $rsp;
		}

		return array('ok' => 1, 'integer' => $rsp['response']['integer']);
	}

	#################################################################

?>
