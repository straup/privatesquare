<?php

	loadlib("privatesquare_checkins");
	loadlib("reverse_geoplanet");

	##############################################################################

	function privatesquare_export_filehandle(){
		$fh = fopen("php://temp", "w");
		return $fh;
	}

	##############################################################################

	function privatesquare_export_is_valid_format($format){

		$valid = privatesquare_export_valid_formats();
		$valid = array_keys($valid);

		return (in_array($format, $valid)) ? 1 : 0;
	}

	##############################################################################

	function privatesquare_export_valid_formats($by_mimetype=0){

		$map = array(
			'csv' => 'text/plain',
			'geojson' => 'application/json',
		);

		if ($by_mimetype){
			$map = array_flip($map);
		}

		return $map;
	}

	##############################################################################

	function privatesquare_export_send(&$fh, &$headers, $more=array()){

		rewind($fh);
		$data = stream_get_contents($fh);

		$headers['Content-length'] = strlen($data);

		if ((! isset($more['filename'])) && (! isset($more['inline']))){

			$map = privatesquare_export_valid_formats("by mimetype");
			$ext = $map[$headers['Content-type']];
			$hash = md5($data);

			$more['filename'] = "privatesquare-{$hash}.{$ext}";
		}

		privatesquare_export_send_headers($headers, $more);

		echo $data;
		exit();
	}

	##############################################################################

	function privatesquare_export_send_headers(&$headers, $more=array()){

		foreach ($headers as $k => $v){
			header("{$k}: {$v}");
		}

		if (! isset($more['inline'])){
			header("Content-Disposition: attachment; filename=\"{$more['filename']}\"");
		}

	}

	##############################################################################

	function privatesquare_export_massage_checkin(&$row, $more=array()){

		$status_map = privatesquare_checkins_status_map();
		$row['status_name'] = $status_map[$row['status_id']];

		# prefix keys with machinetag namespaces?

		if (isset($row['venue'])){
			$row['venue_name'] = $row['venue']['name'];
			unset($row['venue']);
		}

		if ($row['locality']){
			$loc = reverse_geoplanet_get_by_woeid($row['locality'], 'locality');
			$row['locality_name'] = $loc['name'];
		}

		if ((isset($row['weather'])) && (isset($more['inflate_weather']))){

			if ($data = json_decode($row['weather'], 'as hash')){

				foreach ($data as $k => $v){
					$row[ "weather_{$k}" ] = $v;
				}
			}

			unset($row['weather']);			
		}

		# note the pass-by-ref
	}

	##############################################################################
?>
