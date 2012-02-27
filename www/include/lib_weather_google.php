<?php

	loadlib("geo_utils");
	loadlib("http");

	$GLOBALS['weather_google_endpoint'] = 'http://www.google.com/ig/api';

	#################################################################

	function weather_google_conditions($lat, $lon){

		$enc_lat = geo_utils_prepare_coordinate($lat);
		$enc_lon = geo_utils_prepare_coordinate($lon);

		$query = array(
			'weather' => ",,,{$enc_lat},{$enc_lon}",
		);

		$url = $GLOBALS['weather_google_endpoint'] . "?" . http_build_query($query);
	
		$rsp = http_get($url);

		if (! $rsp['ok']){
			return $rsp;
		}

		libxml_use_internal_errors(true);
		$doc = new DOMDocument();

		$doc->loadXML($rsp['body']);
		$xpath = new DOMXpath($doc);

		$cond = $xpath->query("*/current_conditions");

		$current = array();

		foreach ($cond as $c){

			foreach ($c->childNodes as $node){

				$k = $node->nodeName;
				$v = $node->getAttribute("data");

				if ($k == 'icon'){
					continue;
				}

				$current[$k] = $v;
			}

			break;
		}

		if (! count($current)){
			return not_okay("failed to parse conditions");
		}

		$rsp = array(
			'latitude' => $lat,
			'longitude' => $lon,
			'timestamp' => time(),
			'source' => 'google',
			'conditions' => $current,
		);

		return okay($rsp);
	}

	#################################################################

?>
