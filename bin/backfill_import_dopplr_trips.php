<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");

	loadlib("cli");
	loadlib("trips");

	$spec = array(
		"dopplr" => array("flag" => "d", "required" => 1, "help" => "..."),
		"user_id" => array("flag" => "u", "required" => 1, "help" => "..."),
	);

	$opts = cli_getopts($spec);
	$path = $opts['dopplr'];

	$fh = fopen($path, "r");
	$data = fread($fh, filesize($path));
	fclose($fh);

	$data = json_decode($data, "as json");
	$trips = $data['trips'];

	foreach ($trips as $dopplr_trip){

		$trip = array(
			'user_id' => $opts['user_id'],
			'dopplr_id' => $dopplr_trip['id'],
			'arrival' => $dopplr_trip['start'],
			'departure' => $dopplr_trip['finish'],
			'locality_id' => $dopplr_trip['city']['woeid'],
			'status_id' => 1,
		);

		# TO DO: sort out transport type

		dumper($trip);

		$rsp = trips_add_trip($trip);
		# dumper($rsp);
	}

	exit();
?>
