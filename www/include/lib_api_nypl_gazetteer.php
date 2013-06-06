<?php

	loadlib("geo_utils");
	loadlib("nypl_gazetteer");

 	#################################################################

	function api_nypl_gazetteer_search(){

		$lat = request_float('latitude');
		$lon = request_float('longitude');

		if (($lat) && (! geo_utils_is_valid_latitude($lat))){
			api_output_error(999, "Missing or invalid latitude");
		}

		if (($lon) && (! geo_utils_is_valid_longitude($lon))){
			api_output_error(999, "Missing or invalid longitude");
		}

		$checkin_crumb = crumb_generate("api", "privatesquare.venues.checkin");

		#

		$bbox = geo_utils_bbox_from_point($lat, $lon, .5, $unit='m');
		$bbox = implode(",", array($bbox[1], $bbox[0], $bbox[3], $bbox[2]));

		$path = "/place/search.json";

		$args = array(
			'bbox' => $bbox,
			'q' => 'feature_code:BLDG',
		);

		$rsp = nypl_gazetteer_get($path, $args);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		$venues = array();

		foreach ($rsp['data']['features'] as $f){

			$venues[] = array(
				'id' => $f['properties']['id'],
				'name' => $f['properties']['name'],
			);
		}

		$out = array(
			'venues' => $venues,
			'latitude' => $lat,
			'longitude' => $lon,
			'crumb' => $checkin_crumb,
		);

		api_output_ok($out);
	}

 	#################################################################

	# the end
