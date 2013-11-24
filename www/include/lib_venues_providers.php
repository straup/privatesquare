<?php

	#################################################################

	function venues_providers_map($string_keys=0){

		$map = $GLOBALS['cfg']['privatesquare_venues_providers'];

		if ($string_keys){
			$map = array_flip($map);
		}

		return $map;
	}

	#################################################################

	function venues_providers_is_valid_provider($id){

		if (! isset($id)){
			return 0;
		}

		$map = venues_providers_map();
		return (isset($map[$id])) ? 1 : 0;
	}

	#################################################################

	function venues_providers_label_to_id($label){

		$map = venues_providers_map('string keys');
		return (isset($map[$label])) ? $map[$label] : null;
	}

	#################################################################

	function venues_providers_id_to_label($id){

		$map = venues_providers_map();
		return (isset($map[$id])) ? $map[$id] : null;
	}

	#################################################################

	# TO DO: read actual feature flags...

	function venues_providers_is_enabled($label){

		$map = venues_providers_map('string keys');
		return (isset($map[$label])) ? 1 : 0;		
	}

	#################################################################

	function venues_providers_is_valid_provider_id($id){

		$map = venues_providers_map();
		return (isset($map[$id])) ? 1 : 0;
	}

	#################################################################

	# the end
