<?php


	loadlib("reverse_geoplanet");

	#################################################################

	function venues_geo_placetypes(){

		return array(
			'neighbourhood',
			'locality',
			'region',
			'country'
		);
	}

	#################################################################

	function venues_geo_append_hierarchy($lat, $lon, &$thing){

		$geo_rsp = reverse_geoplanet($lat, $lon, $GLOBALS['cfg']['reverse_geoplanet_remote_endpoint']);

		$places = venues_geo_placetypes();
		$data = array();

		if ($geo_rsp['ok']){
			$data = $geo_rsp['data'];
		}

		foreach ($places as $type){
			$thing[$type] = (isset($data[$type])) ? $data[$type] : 0;				
		}

		# note the pass by ref
	}

	#################################################################

	# transfer from $this to $that

	function venues_geo_transfer_hierarchy(&$this, &$that){

		$places = venues_geo_placetypes();

		foreach ($places as $type){
			$that[$type] = (isset($this[$type])) ? $this[$type] : 0;				
		}

		# note the pass by ref
	}

	#################################################################

	# the end
