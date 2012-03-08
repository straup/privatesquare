<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");

	loadlib("privatesquare_checkins");
	loadlib("suncalc_simple");
	loadlib("backfill");

	function _suncalc($checkin){

		$date = date('c', $checkin['created']);

		$lat = $checkin['latitude'];
		$lon = $checkin['longitude'];

		$rsp = suncalc_simple($checkin['created'], $lat, $lon);

		$update = array(
			'timeofday' => strtolower($rsp['timeofday']),
			'sun_inthe_sky' => json_encode($rsp['position']),
		);

		$rsp = privatesquare_checkins_update($checkin, $update);
		echo "{$checkin['id']} : {$update['timeofday']} : {$rsp['ok']}\n";
	}

	$sql = "SELECT * FROM PrivatesquareCheckins";

	backfill_db_users($sql, "_suncalc");
	exit();
	
?>
