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

		if (! $row['tzid']){
			return;
		}

		unset($row['offset']);

		$insert = array();

		foreach ($row as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert('Timezones', $insert);

		echo "{$row['tzid']}: {$rsp['ok']}\n";
	}

	$spec = array(
		"input" => array("flag" => "i", "required" => 1, "help" => "..."),
	);

	$opts = cli_getopts($spec);

	csv_parse_file($opts['input'], 'import_tz');
	exit();

?>
