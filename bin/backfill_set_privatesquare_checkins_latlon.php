<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("backfill");
	loadlib("foursquare_venues");

	function _set_latlon($row, $more=array()){

		$user = users_get_by_id($row['user_id']);

		$venue_id = $row['venue_id'];

		$venue = foursquare_venues_get_by_venue_id($venue_id);

		if (! $venue){
			$venue = foursquare_venues_archive_venue($venue_id);
		}

		if (! $venue){
			echo "can not sort out venue data for '{$venue_id}'\n";
			return;
		}

		$lat = $venue['latitude'];
		$lon = $venue['longitude'];

		$update = array(
			'latitude' => AddSlashes($lat),
			'longitude' => AddSlashes($lon),
		);

		$enc_id = $row['id'];
		$where = "id='{$enc_id}'";

		$cluster_id = $user['cluster_id'];

		$rsp = db_update_users($cluster_id, 'PrivatesquareCheckins', $update, $where);

		echo "{$venue_id} : {$lat}, {$lon} {$where}: {$rsp['ok']}\n";
	}

	$sql = "SELECT * FROM PrivatesquareCheckins";
	backfill_db_users($sql, "_set_latlon");

	exit();

?>
