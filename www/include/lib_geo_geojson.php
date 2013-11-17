<?php

	# see also: git@github.com:phayes/geoPHP.git

	########################################################################

	function geo_geojson_features_to_bbox(&$features){

		$geom = $features['geometry'];

		if (isset($features['bbox'])){
			list($swlon, $swlat, $nelon, $nelat) = explode(",", $features['bbox']);
			return array($swlat, $swlon, $nelat, $nelon);
		}

		else if ($geom['type'] == 'Point'){
			list($lon, $lat) = $geom['coordinates'];
			return geo_utils_bbox_from_point($lat, $lon, 0);
		}

		else if ($geom['type'] == 'Polygon'){

			$swlat = null;
			$swlon = null;
			$nelat = null;
			$nelon = null;

			foreach ($geom['coordinates'] as $poly){

				foreach ($poly as $coord){
					list($lon, $lat) = $coord;
				}

				$swlat = (isset($swlat)) ? min($swlat, $lat) : $lat;
				$swlon = (isset($swlon)) ? min($swlon, $lon) : $lon;
				$nelat = (isset($nelat)) ? max($nelat, $lat) : $lat;
				$nelon = (isset($nelon)) ? max($nelon, $lon) : $lon;
			}

			return array($swlat, $swlon, $nelat, $nelon);
		}

		# TO DO: all those other possibilities...
	}

	########################################################################

	# the end
