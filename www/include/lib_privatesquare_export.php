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
			# this doesn't work yet (20131204/straup)
			# 'ics' => 'text/calendar',			
			'geojson' => 'application/json',
		);

		if ($by_mimetype){
			$map = array_flip($map);
		}

		return $map;
	}

	##############################################################################

	function privatesquare_export_send_headers(&$headers, $more=array()){

		$defaults = array(
			'inline' => 0
		);

		$more = array_merge($defaults, $more);

		if (($more['inline']) && (! preg_match("/^image/", $headers['Content-type']))){
			$headers['Content-type'] = 'text/plain';
		}

		foreach ($headers as $k => $v){
			header("{$k}: {$v}");
		}

		if (! $more['inline']){
		 	header("Content-Disposition: attachment; filename=\"{$more['filename']}\"");
		}

	}

	##############################################################################

	# prefix keys with machinetag namespaces?

	function privatesquare_export_massage_checkin(&$row, $more=array()){

		# inflate: json_decode all the arrays
		# collapse: json_encode all the arrays
		# flatten: flatten all the arrays in to foo_bar_baz key/value pairs - TBW (20131127/straup)

		$defaults = array(
			'inflate_weather' => 0,
			'inflate_all' => 0,
			'collapse_all' => 0,
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

		$inflatables = array(
			'weather',
			'sun_inthe_sky',
			# 'venue',	
		);

		foreach ($inflatables as $what){

			if (! isset($row[$what])){
				continue;
			}

			if (is_array($row[$what])){
				continue;
			}

			$inflate_this = "inflate_{$what}";

			if ((! $more[$inflate_this]) && (! $more['inflate_all'])){
				continue;
			}

			if ($data = json_decode($row[$what], 'as hash')){
				$row[$what] = $data;
			}
		}

		if ($more['collapse_all']){

			foreach ($row as $k => $v){
				if (is_array($v)){
					$row[$k] = json_encode($v);
				}
			}
		}

		# TBW: flatten...

		# note the pass-by-ref
	}

	##############################################################################

	# the end
