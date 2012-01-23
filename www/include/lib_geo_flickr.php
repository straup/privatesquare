<?php

	#
	# $Id$
	#

	loadlib("flickr_api");
	loadlib("geohash");

	#################################################################

	function geo_flickr_reverse_geocode($lat, $lon){

		$short_lat = (float)sprintf("%.3f", $lat);
		$short_lon = (float)sprintf("%.3f", $lon);

		$geohash = geohash_encode($short_lat, $short_lon);
		$cache_key = "flickr_reversegeocode_{$geohash}";

		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return $cache['data'];
		}

		$args = array(
			'lat' => $short_lat,
			'lon' => $short_lon,
		);

		$rsp = flickr_api_call('flickr.places.findByLatLon', $args);

		if (! $rsp['ok']){
			return;
		}

		$loc = $rsp['rsp']['places']['place'][0];
		cache_set($cache_key, $loc, 'set locally');

		return $loc;
	}

	#################################################################

	function geo_flickr_get_woeid($woeid){

		$cache_key = "flickr_woeid_{$woeid}";

		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return $cache['data'];
		}

		$args = array(
			'woe_id' => $woeid,
		);

		$rsp = flickr_api_call('flickr.places.getInfo', $args);

		if (! $rsp['ok']){
			return;
		}

		$loc = $rsp['rsp']['place'];
		cache_set($cache_key, $loc, 'set locally');

		return $loc;
	}

	#################################################################

?>
