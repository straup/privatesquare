<?php

	#################################################################

	function venues_providers_map($string_keys=0){

		$map = array(
			0 => 'user',
			1 => 'foursquare',
			2 => 'stateofmind',
		);

		if ($string_keys){
			$map = array_flip($map);
		}

		return $map;
	}

	#################################################################

	function venues_providers_is_valid_provider($id){

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

	function venues_providers_is_valid_provider_id($id){

		$map = venues_providers_map();
		return (isset($map[$id])) ? 1 : 0;
	}

	#################################################################

	# the end
