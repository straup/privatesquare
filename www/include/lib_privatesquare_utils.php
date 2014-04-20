<?php

	#################################################################

	function privatesquare_utils_generate_id($len=64){

		$use_artisanal = 0;

		if ((features_is_enabled("artisanal_integers")) && ($len==64)){
			$use_artisanal = 1;
		}

		if (! $use_artisanal){
			$id = dbtickets_create($len);
		}

		else {
			$id = privatesquare_utils_generate_artisanal_id();
		}

		if (! $id){
			return array('ok' => 0, 'error' => "failed to generate id (artisanal: {$use_artisanal})");
		}

		return array('ok' => 1, 'id' => $id, 'is_artisanal' => $use_artisanal);
	}

	#################################################################

	function privatesquare_utils_generate_artisanal_id($max_attempts=3){

		loadlib("artisanal_integers");		

		$provider = null;	# random

		if (isset($GLOBALS['cfg']['artisanal_integers_provider'])){

			if (artisanal_integers_is_valid_provider($GLOBALS['cfg']['artisanal_integers_provider'])){
				$provider = $GLOBALS['cfg']['artisanal_integers_provider'];
			}
		}

		$attempts = 0;
		$id = 0;

		while (! $id){

			$attempts += 1;

			$rsp = artisanal_integers_create($provider);

			if ($rsp['ok']){
				$id = $rsp['integer'];
				break;
			}

			# log_notice("failed to return integer: {$rsp['error']}");

			if ($rsp['error_code'] == -1){
				# log_notice($rsp['error']);
				break;
			}

			if ($attempts == $max_attempts){
				# log_notice("exceeded max attempts to collect an artisanal integer");
				break;
			}
		}

		return $id;
	}

	#################################################################

	# the end
