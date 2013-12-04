<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("cli");

	loadlib("privatesquare_export");
	loadlib("privatesquare_checkins_export");

	$spec = array(
		'u' => array('name' => 'user', 'required' => 1, 'help' => '...'),
		'f' => array('name' => 'format', 'required' => 1, 'help' => '...'),
		'o' => array('name' => 'output', 'required' => 0, 'help' => '...'),
	);

	$opts = cli_getopts($spec);

	$user_id = $opts['u'];
	$format = $opts['f'];

	$user = users_get_by_id($user_id);

	if (! $user){
		echo "Invalid user ID\n";
		exit();
	}

	if (! privatesquare_export_is_valid_format($format)){
		echo "Invalid format\n";
		exit();
	}

	$output = ($opts['o']) ? $opts['o'] : 'php://stdout';
	$fh = fopen($output, 'w');

	$fetch_what = array(
		'user_id' => $user['id'],
	);

	# TO DO: ADD MORE THINGS TO FILTER BY

	$export_lib = "privatesquare_export_{$format}";
	$export_func = "privatesquare_export_{$format}_row";

	loadlib($export_lib);

	privatesquare_checkins_export($fetch_what, $export_func, $fh);
	exit();

?>
