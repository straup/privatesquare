<?php

	loadlib("export");

	##############################################################################

	function export_geojson(&$fh, &$checkins){

		$features = array();

		$swlat = null;
		$swlon = null;
		$nelat = null;
		$nelon = null;

		foreach ($checkins as $row){

			export_massage_checkin($row);

			$lat = floatval($row['latitude']);
			$lon = floatval($row['longitude']);

			$swlat = (isset($swlat)) ? min($swlat, $lat) : $lat;
			$swlon = (isset($swlon)) ? min($swlon, $lon) : $lon;
			$nelat = (isset($nelat)) ? max($nelat, $lat) : $lat;
			$nelon = (isset($nelon)) ? max($nelon, $lon) : $lon;

			$features[] = array(
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

		return okay();
	}

	##############################################################################
