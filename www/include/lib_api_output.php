<?php

	loadlib("api_output_utils");

	# Hey look! Running code!!

	$format = api_output_get_format();

	if (! $format){
		$format = $GLOBALS['cfg']['api']['default_format'];
	}

	loadlib("api_output_{$format}");

	#################################################################

	function api_output_get_format(){

		$format = null;
		$possible = null;
	
		if (request_isset('format')){
			$possible = request_str('format');
		}

		elseif (function_exists('getallheaders')){

			$headers = getallheaders();

			if (isset($headers['Accept'])){

				foreach (explode(",", $headers['Accept']) as $what){

					$parts = explode(";", $what, 2);

					if ((count($parts) == 2) && (preg_match("!^application/(\w+)$!", $parts[0], $m))){
						$possible = $m[1];
						break;
					}
				}
			}
		}

		else {}

		if ($possible){

			if (isset($GLOBALS['cfg']['api']['formats'][$possible])){

				$details = $GLOBALS['cfg']['api']['formats'][$possible];

				if ($details['enabled']){
					$format = $possible;
				}
			}
		}

		# this is pretty much entirely so that can do this and have
		# it resolve to ?format=chicken (20170501/thisisaaronland)
		# ?method=whosonfirst.places.getInfo&id=420561633&format=🐔 

		if (! $format){

			foreach ($GLOBALS['cfg']['api']['formats'] as $fmt => $details){

				if (! $details['enabled']){
					continue;
				}

				if ((! isset($details['alt'])) || (! is_array($details['alt']))){
					continue;
				}

				if (in_array($possible, $details['alt'])){
					$format = $fmt;
					break;
				}
			}
		}

		if (! $format){
			$format = $GLOBALS['cfg']['api']['default_format'];
		}

		return $format;
	}

	#################################################################

	# the end
