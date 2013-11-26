<?php

	loadlib("privatesquare_export");

	# THIS IS BROKEN AND NEEDS TO BE UPDATED TO WORK WITH php://output
	# OR CONVINCE EVERYTHING ELSE TO WRITE TO php://temp (20131126/straup)

	##############################################################################

	function privatesquare_export_geojson($fh, $checkins, $more=array()){

		$features = array();

		$swlat = null;
		$swlon = null;
		$nelat = null;
		$nelon = null;

		foreach ($checkins as $row){

			# See notes in privatesquare_export_csv for why we're
			# doing this explicitly (20120227/straup)

			$_more = array(
				'inflate_weather' => 1,
			);

			privatesquare_export_massage_checkin($row, $_more);

			$lat = floatval($row['latitude']);
			$lon = floatval($row['longitude']);

			$swlat = (isset($swlat)) ? min($swlat, $lat) : $lat;
			$swlon = (isset($swlon)) ? min($swlon, $lon) : $lon;
			$nelat = (isset($nelat)) ? max($nelat, $lat) : $lat;
			$nelon = (isset($nelon)) ? max($nelon, $lon) : $lon;

			$features[] = array(
				'type' => 'Feature',
				'id' => $row['id'],
				'properties' => $row,
				'geometry' => array(
					'type' => 'Point',
					'coordinates' => array($lon, $lat),
				),
			);
		}

		$geojson = array(
			'type' => 'FeatureCollection',
			'bbox' => array($swlon, $swlat, $nelon, $nelat),
			'features' => $features,
		);

		fwrite($fh, json_encode($geojson));

		if (isset($more['donot_send'])){
			return okay();
		}

		$map = privatesquare_export_valid_formats();

		$headers = array(
			'Content-type' => $map['geojson'],
		);

		privatesquare_export_send($fh, $headers, $more);
	}

	##############################################################################

	# THIS HAS NOT BEEN TESTED YET (20131126/straup)

	function privatesquare_export_geojson_row($row, $fh, $more=array()){

		$defaults = array(
			'index' => 0,
			'send_headers' => 1
		);

		$more = array_merge($defaults, $more);

		$massage_more = array(
			'inflate_all' => 1,
		);

		privatesquare_export_massage_checkin($row, $massage_more);
		ksort($row);

		$lat = floatval($row['latitude']);
		$lon = floatval($row['longitude']);

		$feature = array(
			'type' => 'Feature',
			'id' => $row['id'],
			'properties' => $row,
			'geometry' => array(
				'type' => 'Point',
				'coordinates' => array($lon, $lat),
			),
		);

		/*
		$geojson = array(
			'type' => 'FeatureCollection',
			'features' => array(),
		);
		*/

		if (($more['is_first']) && ($more['send_headers'])){

			$map = privatesquare_export_valid_formats();

			$headers = array(
				'Content-type' => $map['geojson'],
			);

			privatesquare_export_send_headers($headers);

			# SEND FeatureCollection crap here...
		}

		fwrite($fh, json_encode($feature));

		if (! $more['is_last']){
			fwrite($fh, ",");	# sad face...
		}

		else {
			# SEND END OF FeatureCollection crap here...
		}

		if (isset($more['donot_send'])){
			return okay();
		}

	}

	##############################################################################

	# the end
