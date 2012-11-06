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

		# TO DO: error checking / handling - as in: what
		# should actually happen if an API fails?
		# (20121022/straup)
		
		loadlib("artisanal_integers");
		$rsp = artisanal_integers_create();

		if (! $rsp['ok']){
			return 0;
		}

		return $rsp['integer'];
	}

	#################################################################
?>
