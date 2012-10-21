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

		loadlib("artisanal_integers");
		return artisanal_integers_create();
	}

	#################################################################
?>
