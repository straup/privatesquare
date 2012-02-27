<?php

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

	function privatesquare_export_massage_checkin(&$row){

		# prefix keys with machinetag namespaces?

		if (isset($row['venue'])){
			$row['venue'] = $row['venue']['name'];
		}

		# note the pass-by-ref
	}

	##############################################################################
?>
