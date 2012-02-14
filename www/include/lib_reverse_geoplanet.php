<?php

	loadlib("geohash");
	loadlib("geo_flickr");

	########################################################################

	function reverse_geoplanet($lat, $lon, $remote_endpoint=''){

		# this takes care of its own caching

		if ($remote_endpoint){
			return _reverse_geoplanet_remote($lat, $lon, $remote_endpoint);
		}

		$cache_key = _reverse_geoplanet_cache_key($lat, $lon);

		# try to pull it out of memcache

		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return okay($cache);
		}

		# try to pull it out of the local db

		list($short_lat, $short_lon, $geohash) = _reverse_geoplanet_shorten($lat, $lon);
		$enc_hash = AddSlashes($geohash);

		$sql = "SELECT * FROM reverse_geoplanet WHERE geohash='{$enc_hash}'";
		$rsp = db_single(db_fetch($sql));

		if ($rsp){

			cache_set($cache_key, $rsp, "cache locally");

			return okay(array(
				'data' => $rsp
			));
		}

		# try to pull it out of flickr

		$loc = geo_flickr_reverse_geocode($lat, $lon);

		if (! $loc){
			return not_okay("failed to reverse geocode");
		}

		$woeid = $loc['woeid'];

		$loc = geo_flickr_get_woeid($loc['woeid']);

		if (! $loc){
			return not_okay("failed to retrieve data for WOE ID '{$loc['woeid']}");
		}

		if (! $loc['woeid']){
			return not_okay("failed to parse data for WOE ID '{$loc['woeid']}");
		}

		#

		$hierarchy = array();

		foreach (array('locality', 'region', 'country') as $placetype){
			$hierarchy[$placetype] = $loc[$placetype]['woeid'];
		}

		$now = time();

		$data = array(
			'latitude' => $short_lat,
			'longitude' => $short_lon,
			'geohash' => $geohash,
			'woeid' => $loc['woeid'],
			'locality' => $hierarchy['locality'],
			'region' => $hierarchy['region'],
			'country' => $hierarchy['country'],
			'name' => $loc['name'],
			'placetype' => $loc['place_type_id'],
			'created' => $now,
		);

		$rsp = reverse_geoplanet_add($data);

		if (! $rsp['ok']){
			return $rsp;
		}

		return $rsp;
	}

	########################################################################

	function _reverse_geoplanet_remote($lat, $lon, $remote_endpoint){

		$cache_key = _reverse_geoplanet_cache_key($lat, $lon);

		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return okay($cache);
		}

		#

		$query = http_build_query(array(
			'lat' => $lat,
			'lon' => $lon,
		));

		$url = "{$remote_endpoint}?{$query}";

		$rsp = http_get($url);

		if (! $rsp['ok']){
			return $rsp;
		}

		$data = json_decode($rsp['body'], 'as hash');

		if (! $data){
			return not_okay("failed to parse response");
		}

		#

		cache_set($cache_key, $data, "cache locally");

		return okay(array(
			'data' => $data,
			'source' => $remote_endpoint,
		));
	}

	########################################################################

	# this is useful for pre-populating the database... I guess
	# (20120121/straup)

	function reverse_geoplanet_add($data){

		$insert = array();

		foreach ($data as $key => $value){
			$insert[$key] = AddSlashes($value);
		}

		$rsp = db_insert('reverse_geoplanet', $insert);

		if ($rsp['ok']){

			$cache_key = _reverse_geoplanet_cache_key($data['latitude'], $data['longitude']);
			cache_set($cache_key, $data, 'cache locally');

			$rsp['data'] = $data;
		}

		return $rsp;
	}

	########################################################################

	function _reverse_geoplanet_shorten($lat, $lon){

		$short_lat = (float)sprintf("%.3f", $lat);
		$short_lon = (float)sprintf("%.3f", $lon);
		$geohash = geohash_encode($short_lat, $short_lon);

		return array($short_lat, $short_lon, $geohash);
	}

	########################################################################

	function _reverse_geoplanet_cache_key($lat, $lon){
		list($short_lat, $short_lon, $geohash) = _reverse_geoplanet_shorten($lat, $lon);
		return "reversegeocode_full_{$geohash}";
	}

	########################################################################
?>
