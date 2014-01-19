<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");

	loadlib("cli");
	loadlib("trips");
	loadlib("trips_dopplr");

	$spec = array(
		"dopplr" => array("flag" => "d", "required" => 1, "help" => "..."),
		"user_id" => array("flag" => "u", "required" => 1, "help" => "..."),
	);

	$opts = cli_getopts($spec);
	$path = $opts['dopplr'];

	$user = users_get_by_id($opts['user_id']);

	$fh = fopen($path, "r");
	$data = fread($fh, filesize($path));
	fclose($fh);

	$data = json_decode($data, "as json");
	$trips = $data['trips'];

	foreach ($trips as $dopplr_trip){

		$rsp = trips_dopplr_import_trip($dopplr_trip, $user);
		echo "import dopplr trip {$dopplr_trip['id']}: {$rsp['ok']}\n";
	}

	exit();
?>
