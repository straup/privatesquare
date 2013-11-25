<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");

	loadlib("backfill");
	loadlib("cli");

	loadlib("privatesquare_checkins");
	loadlib("privatesquare_export_geojson");
	loadlib("privatesquare_export_csv");

	$GLOBALS['fh'] = fopen("php://stdout", "w");
	$GLOBALS['header'] = 0;

	# TO DO: please reconcile me with all the other export stuff.
	# Or not, maybe? E_EXCESSIVE_FUSSY... (20131125/straup)

	function export_checkins($row, $more=array()){

		$row['venue'] = venues_get_by_venue_id($row['venue_id']);
		privatesquare_export_massage_checkin($row);

		ksort($row);

		if (! $GLOBALS['header']){
			$keys = array_keys($row);
			fputcsv($GLOBALS['fh'], $keys);

			$GLOBALS['header'] = 1;
		}

		fputcsv($GLOBALS['fh'], $row);
	}

	# main()

	$spec = array(
		'u' => array('name' => 'user', 'required' => 0, 'help' => '...'),
	);

	$opts = cli_getopts($spec);

	if (! $opts['u']){
		echo "Missing user\n";
		exit();
	}

	$user = users_get_by_id($opts['u']);

	if (! $user){
		echo "Invalid user ID\n";
		exit();
	}

	$sql_more = array();

	foreach ($possible as $what){

		if (isset($options[$what])){
			$sql_more[$what] = $options[$what];
		}
	}

	$backfill_more = array('cluster_id' => $user['cluster_id']);

	$sql = privatesquare_checkins_for_user_sql($user, $sql_more);
	backfill_db_users($sql, "export_checkins", $backfill_more);

	exit();

?>
