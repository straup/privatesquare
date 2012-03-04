<?php

	loadlib("geo_utils");

 	#################################################################

	function privatesquare_checkins_utils_geo_stats($checkins){

		if (count($checkins) == 1){
			$lat = $checkins[0]['latitude'];
			$lon = $checkins[0]['longitude'];

			$bbox = geo_utils_bbox_from_point($lat, $lon, 1);

			return array(
				'centroid' => array($lat, $lon),
				'bounding_box' => $bbox,
			);
		}

		$swlat = null;
		$swlon = null;
		$nelat = null;
		$nelon = null;

		foreach ($checkins as $row){
			$lat = $row['latitude'];
			$lon = $row['longitude'];

			$swlat = (isset($swlat)) ? min($swlat, $lat) : $lat;
			$swlon = (isset($swlon)) ? min($swlon, $lon) : $lon;
			$nelat = (isset($nelat)) ? max($nelat, $lat) : $lat;
			$nelon = (isset($nelon)) ? max($nelon, $lon) : $lon;
		}

		$ctr_lat = $swlat + (($nelat - $swlat) / 2);
		$ctr_lon = $swlon + (($nelon - $swlon) / 2);

		return array(
			'centroid' => array($ctr_lat, $ctr_lon),
			'bounding_box' => array($swlat, $swlon, $nelat, $nelon),
		);
	}

 	#################################################################
?>
