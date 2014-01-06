<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("backfill");

	loadlib("privatesquare_checkins_dates");
	loadlib("privatesquare_checkins_timezones");

	function set_timezone($row){

		$tz = privatesquare_checkins_timezones_get_timezone($row);

		$update = array(
			'timezone' => $tz,
		);

		$mock = $row;
		$mock['timezone'] = $tz;

		$ymd = privatesquare_checkins_dates_format_ymd($mock);
		$update['ymd'] = $ymd;

		if (! $ymd){
			echo "failed to determine timezone for check-in ID '{$row['id']}\n";
			return;
		}

		list($year, $month, $day) = explode("-", $ymd);
		$update['year'] = $year;
		$update['month'] = $month;
		$update['day'] = $day;

		# echo json_encode($update) . "\n";
		# return;

		$rsp = privatesquare_checkins_update($row, $update);
		echo "{$row['id']} – {$tz}: {$rsp['ok']}\n";
	}

	$sql = "SELECT * FROM PrivatesquareCheckins";
	backfill_db_users($sql, "set_timezone");

	exit();

?>
