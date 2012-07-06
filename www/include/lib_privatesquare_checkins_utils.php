<?php

	loadlib("geo_utils");

 	#################################################################

	function privatesquare_checkins_utils_geo_stats($checkins, $more=array()){

		$defaults = array(
			'enbiggen_bbox' => 1,
		);

		$more = array_merge($defaults, $more);

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

		# typically so that when creating a map by extent we don't
		# crop items (dots) that hug the edge of the map/bbox

		if (isset($more['enbiggen_bbox'])){

			$sw_bbox = geo_utils_bbox_from_point($swlat, $swlon, .5);
			$ne_bbox = geo_utils_bbox_from_point($nelat, $nelon, .5);

			$swlat = $sw_bbox[0];
			$swlon = $sw_bbox[1];
			$nelat = $ne_bbox[2];
			$nelon = $ne_bbox[3];
		}

		$ctr_lat = $swlat + (($nelat - $swlat) / 2);
		$ctr_lon = $swlon + (($nelon - $swlon) / 2);

		return array(
			'centroid' => array($ctr_lat, $ctr_lon),
			'bounding_box' => array($swlat, $swlon, $nelat, $nelon),
		);
	}

 	#################################################################

	function privatesquare_checkins_utils_has_visited_venue(&$user, $venue_id){

		$more = array(
			'venue_id' => $venue_id
		);

		$statuses = privatesquare_checkins_statuses_for_user($user, $more);

		if (count(array_keys($statuses)) > 1){
			$has_visited = 1;
		}

		else if (isset($statuses['2'])){
			$has_visited = 0;
		}

		else if (count(array_keys($statuses))){
			$has_visited = 1;
		}

		return $has_visited;
	}

 	#################################################################
?>
