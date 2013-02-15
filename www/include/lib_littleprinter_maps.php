<?php

	include_once FLAMEWORK_INCLUDE_DIR . "/modestmaps/ModestMaps.php";

	########################################################################

	function littleprinter_maps_tile_url($lat, $lon, $zoom=15){

		$provider = new MMaps_OpenStreetMap_Provider();
		$pt = new MMaps_Location($lat, $lon);

		$coord = $provider->locationCoordinate($pt);
		$coord = $coord->zoomTo($zoom);

		$x = intval($coord->column);
		$y = intval($coord->row);
		$z = intval($coord->zoom);

		return "http://tile.stamen.com/toner/{$z}/{$x}/{$y}.jpg";
	}

	########################################################################

	# the end
