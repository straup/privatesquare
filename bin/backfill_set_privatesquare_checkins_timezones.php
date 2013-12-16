<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("backfill");
	loadlib("privatesquare_checkins_timezones");

	# THIS HAS NOT BEEN TESTED PROPERLY (20131216/straup)

	function _set_timezone($row, $more=array()){

		$tz = privatesquare_checkins_timezones_get_timezone($row);

		$update = array(
			'timezone' => $tz
		);

		echo "$tz\n";
		return;

		$enc_id = $row['id'];
		$where = "id='{$enc_id}'";

		$user = users_get_by_id($row['user_id']);
		$cluster_id = $user['cluster_id'];

		$rsp = db_update_users($cluster_id, 'PrivatesquareCheckins', $update, $where);

		echo "{$where} is {$tz}: {$rsp['ok']}\n";
	}

	$sql = "SELECT * FROM PrivatesquareCheckins";
	backfill_db_users($sql, "_set_timezone");

	exit();

?>
