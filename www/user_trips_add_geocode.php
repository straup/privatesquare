<?php

	include("include/init.php");
	loadlib("flickr_api");

	if (! login_check_login()){
		exit();
	}

	$query = get_str("q");

	$method = 'flickr.places.find';

	$args = array(
		'query' => $query
	);

	# TO DO: http timeout nonsense...

	$rsp = flickr_api_call($method, $args);

	if (! $rsp['ok']){
		exit();
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

	header("Content-type: text/javascript");
	header("Access-Control-Allow-Origin: {$GLOBALS['cfg']['abs_root_url']}");

	$rsp = array(
		'results' => $results,
	);

	echo json_encode($rsp);
	exit();
?>
