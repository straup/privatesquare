<?php

	loadlib("privatesquare_checkins");
	loadlib("reverse_geoplanet");

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

	function privatesquare_export_send_headers(&$headers, $more=array()){

		foreach ($headers as $k => $v){
			header("{$k}: {$v}");
		}

		if (! isset($more['inline'])){
#		 	header("Content-Disposition: attachment; filename=\"{$more['filename']}\"");
		}

	}

	##############################################################################

	# prefix keys with machinetag namespaces?

	function privatesquare_export_massage_checkin(&$row, $more=array()){

		$defaults = array(
			'inflate_weather' => 0,
			'inflate_all' => 0,
			'flatten_all' => 0,
		);

		$more = array_merge($defaults, $more);

		if ($row['checkin_id']){
			$row['provider_checkin_id'] = $row['checkin_id'];
			unset($row['checkin_id']);
		}

		$status_map = privatesquare_checkins_status_map();
		$row['status_name'] = $status_map[$row['status_id']];

		if (isset($row['venue'])){
			$row['venue_name'] = $row['venue']['name'];
			$row['provider_id'] = $row['venue']['provider_id'];
			$row['provider_venue_id'] = $row['venue']['provider_venue_id'];
			unset($row['venue']);
		}

		# TODO: better geo dumps

		$places = array(
			'neighbourhood',
			'locality',
			'region',
			'country'
		);

		foreach ($places as $type){

			if ((! $row[$type]) && ($row['venue']) && ($row['venue'][$type])){
				$row[$type] = $row['venue'][$type];
			}
		}

		if ((isset($row['weather'])) && (($more['inflate_weather']) || ($more['inflate_all']))){

			if (is_array($row['weather'])){
				$data = $row['weather'];
			}

			else {
				$data = json_decode($row['weather'], 'as hash');
			}

			if (is_array($data)){

				foreach ($data as $k => $v){
					$row[ "weather_{$k}" ] = $v;
				}
			}
		
			unset($row['weather']);			
		}

		if ($more['flatten_all']){

			foreach ($row as $k => $v){
				if (is_array($v)){
					$row[$k] = json_encode($v);
				}
			}
		}

		# note the pass-by-ref
	}

	##############################################################################

	# deprecated... (20131126/straup)

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

	function privatesquare_export_filehandle(){
		$fh = fopen("php://temp", "w");
		return $fh;
	}

	##############################################################################

	# the end
