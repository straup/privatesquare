<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("backfill");

	loadlib("privatesquare_checkins_timezones");

	# THIS HAS NOT BEEN TESTED YET (20131208/straup)

	function set_timezone($row){

		if ($row['timezone']){
			return;
		}

		$tz = privatesquare_checkins_timezones_get_timezone($row);

		$update = array(
			'timezone' => $tz,
		);

		$rsp = privatesquare_checkins_update($row, $update);
		echo "{$row['id']} – {$tz}: {$rsp['ok']}\n";
	}

	$sql = "SELECT * FROM PrivatesquareCheckins";
	backfill_db_users($sql, "set_timezone");

	exit();

?>
