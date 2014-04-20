<?php

	########################################################################

	function api_privatesquare_geo_geocode(){

		$query = get_str("q");

		if (! $query){
			api_output_error(400, "Missing query");
		}

		$method = 'flickr.places.find';

		$args = array(
			'query' => $query
		);

		# TO DO: http timeout nonsense...

		$rsp = flickr_api_call($method, $args);

		if (! $rsp['ok']){
			api_output_error(500, $rsp['error']);
		}

		$rsp = $rsp['rsp'];

		$results = array();

		foreach ($rsp['places']['place'] as $pl){

			if ($pl['place_type'] != 'locality'){
				continue;
			}

			$results[] = array(
				'id' => $pl['woeid'],
				'text' => $pl['_content'],
			);
		}

		$out = array(
			'results' => $results,
		);

		api_output_ok($out);
	}

	########################################################################

	# the end
