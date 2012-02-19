<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");

	loadlib("foursquare_api");
	loadlib("backfill");

	function sync_user($fsq_user, $more=array()){

		$user = users_get_by_id($fsq_user['user_id']);

		$method = 'users/self/checkins';

		$args = array(
			'oauth_token' => $fsq_user['oauth_token'],
			'limit' => 250,
		);

		$rsp = foursquare_api_call($method, $args);

		# do stuff here...
	}

	$sql = "SELECT * FROM FoursquareUsers";
	backfill_db_users($sql, "sync_user");

	exit();
?>
