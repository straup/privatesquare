<?php

	#################################################################

	function privatesquare_utils_generate_id($len=64){

		$use_artisanal = 0;

		if ((features_is_enabled("artisanal_integers")) && ($len==64)){
			$use_artisanal = 1;
		}

		if (! $use_artisanal){
			return dbtickets_create($len);
		}

		return privatesquare_utils_generate_artisanal_id();
	}

	#################################################################

	function privatesquare_utils_generate_artisanal_id($max_attempts=3){

		loadlib("artisanal_integers");		

		$provider = null;	# random
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
?>
