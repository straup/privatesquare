<?php

	#################################################################

	function api_formats_ensure_enabled(&$formats=array()){

		$enabled = array();

		foreach ($formats as $fmt){

			$details = $GLOBALS["cfg"]["api"]["formats"][$fmt];

			if (! $details){
				continue;
			}

			if (! $details["enabled"]){
				continue;
			}

			$enabled[] = $fmt;
		}

		return $enabled;
	}

	$GLOBALS["smarty"]->registerPlugin("modifier", "api_formats_ensure_enabled", "api_formats_ensure_enabled");
	
	#################################################################

	# the end