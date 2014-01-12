<?php

	include("include/init.php");
	loadlib("flickr_api");

	# THIS IS SO NOT FINISHED. OR EVEN NECESSARILY
	# GOING TO WORK THIS WAY... (20140109/straup)

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

	$rsp = array(
		'results' => $results,
	);

	echo json_encode($rsp);
	exit();
?>
