<?php

	loadlib("privatesquare_export");

	##############################################################################

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

		if ($more['is_first']){

			# Grnnnnngnnnhnhnnnh...
			fwrite($fh, '{"type":"FeatureCollection", "features":[');
		}

		fwrite($fh, json_encode($feature));

		if (! $more['is_last']){
			fwrite($fh, ",");	# sad face...
		}

		else {
			fwrite($fh, "]}");	# sad sad face... is sad
		}

	}

	##############################################################################

	# the end
