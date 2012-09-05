<?php

	loadlib("geo_utils");
	loadlib("http");

	$GLOBALS['weather_yahoo_endpoint'] = 'http://weather.yahooapis.com/forecastrss';

	#################################################################

	function weather_yahoo_conditions($lat, $lon, $woe_id){

		$query = array();
		$query['w'] = $woe_id;

		$url = $GLOBALS['weather_yahoo_endpoint'] . "?" . http_build_query($query);
	
		$rsp = http_get($url);

		if (! $rsp['ok']){
			return $rsp;
		}

		$doc = new SimpleXMLElement($rsp['body']);
		$doc->registerXPathNamespace('yweather', 'http://xml.weather.yahoo.com/ns/rss/1.0');
		$conditions = $doc->xpath('//yweather:condition');

		if (! $conditions){
			return not_okay("failed to parse conditions");
		}

		$condition_attrs = $conditions[0]->attributes();

		$current = (string)$condition_attrs['temp'] . "&deg; ";
		$current .= (string)$condition_attrs['text'];

		$rsp = array(
			'latitude' => $lat,
			'longitude' => $lon,
			'timestamp' => time(),
			'source' => 'yahoo',
			'conditions' => $current,
		);

		return okay($rsp);
	}

	#################################################################

?>
