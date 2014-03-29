<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("backfill");

	function do_this($row){

		$dow = privatesquare_checkins_dates_format($row, 'w');

		$update = array(
			'dow' => $dow,
		);

		privatesquare_checkins_update($row, $update);
	}

	$sql = "SELECT * FROM PrivatesquareCheckins";
	backfill_db_users($sql, "do_this");

	exit();

?>
