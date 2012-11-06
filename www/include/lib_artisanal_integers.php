<?php

	#################################################################

	$GLOBALS['cfg']['artisanal_integers_providers'] = array(
		'brooklyn',
		'london',
		'mission',
	);

	#################################################################

	function artisanal_integers_create($provider=null){

		if (! features_is_enabled("artisanal_integers")){
			return failure("artisanal integers are currently disabled", -1);
		}

		if ($provider){

			if (! in_array($provider, $GLOBALS['cfg']['artisanal_integers_providers'])){
				return failure("invalid provider");
			}
		}

		else {

			$count = count($GLOBALS['cfg']['artisanal_integers_providers']);

			if (! $count){
				return failure("no providers defined");
			}

			$idx = rand(1, $count) - 1;
			$provider = $GLOBALS['cfg']['artisanal_integers_providers'][$idx];
		}

		$func_name = "artisanal_integers_create_{$provider}_integer";

		if (! function_exists($func_name)){
			return failure("no handler defined for {$provider}");
		}

		$rsp = call_user_func($func_name);

		if (! $rsp['ok']){
			return 0;
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
			return rsp;
		}

		return success(array(
			'integer' => $rsp['response']['integer']
		));
	}

	#################################################################

	function artisanal_integers_create_mission_integer(){

		loadlib("mission_integers_api");

		$method = 'next-int';
		$rsp = mission_integers_api_call($method);

		if (! $rsp['ok']){
			return $rsp;
		}

		return success(array(
			'integer' => $rsp['response'][0]
		));
	}

	#################################################################

	function artisanal_integers_create_london_integer(){

		loadlib("london_integers_api");

		$method = 'london.integers.create';
		$rsp = london_integers_api_call($method);

		if (! $rsp['ok']){
			return $rsp;
		}

		return success(array(
			'integer' => $rsp['response']['integer']
		));
	}

	#################################################################

?>
