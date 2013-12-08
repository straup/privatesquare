<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("backfill");

	loadlib("privatesquare_checkins_dates");
	loadlib("privatesquare_checkins_timezones");

	# THIS HAS NOT BEEN TESTED YET (20131208/straup)

	function set_timezone($row){

		$tz = privatesquare_checkins_timezones_get_timezone($row);

		$update = array(
			'timezone' => $tz,
		);

		$ymd = privatesquare_checkins_dates_format_ymd($row);
		$update['ymd'] = $ymd;

		list($year, $month, $day) = explode("-", $ymd);
		$update['year'] = $year;
		$update['month'] = $month;
		$update['day'] = $day;

		$rsp = privatesquare_checkins_update($row, $update);
		echo "{$row['id']} – {$tz}: {$rsp['ok']}\n";
	}

	$sql = "SELECT * FROM PrivatesquareCheckins";
	backfill_db_users($sql, "set_timezone");

	exit();

?>
