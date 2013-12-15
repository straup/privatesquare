<?php

	# Strictly speaking this shouldn't be necessary as the full dump
	# of the Timezones database is included in the `schema` directory
	# but just in case... (20131208/straup)

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("cli");
	loadlib("csv");

	function import_tz($row){

		if (! isset($row['woeid'])){
			return;
		}

		$woeid = $row['woeid'];
		$geom = $row['geom'];

		$geom = json_decode($geom, 'as hash');

		# THIS IS BROKEN... THE US IS A MULTIPOLYGON BECAUSE...

		$geom = ($geom['type'] == 'MultiPolygon') ? $geom['coordinates'][0][0] : $geom['coordinates'][0];

		$coords = array();

		foreach ($geom as $pt){
			$coords[] = array($pt[1], $pt[0]);
		}

		$coords = json_encode($coords);

		$update = array(
			'coords' => $coords,
		);

		$enc_woeid = AddSlashes($woeid);
		$where = "woeid='{$enc_woeid}'";

		$rsp = db_update('Timezones', $update, $where);

		echo "update {$enc_woeid} {$row['tzid']} : {$rsp['ok']}\n";

	}

	$spec = array(
		"input" => array("flag" => "i", "required" => 1, "help" => "..."),
	);

	$opts = cli_getopts($spec);

	csv_parse_file($opts['input'], 'import_tz');
	exit();

?>
