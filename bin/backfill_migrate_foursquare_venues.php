<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("backfill");
	loadlib("venues_geo");

	# THIS HAS NOT BEEN TESTED YET (20131124/straup)

	function migrate_venue($row){

		$venue = $row;

		$venue['provider_id'] = venues_providers_label_to_id("foursquare");
		$venue['provider_venue_id'] = $row['venue_id'];

		if ((isset($venue['latitude'])) && (isset($venue['longitude']))){
			venues_geo_append_hierarchy($venue['latitude'], $venue['longitude'], $venue);
		}

		dumper($venue);
		return;

		$rsp = venues_add_venue($venue);

		if (! $rsp['ok']){
			dumper($rsp);
			exit;
		}

		$venue_id = $rsp['venue']['venue_id'];
		$foursquare_id = $rsp['venue']['provider_venue_id'];

		$enc_venue_id = AddSlashes($venue_id);
		$enc_foursquare_id = AddSlashes($foursquare_id);

		$sql = "UPDATE PrivatesquareCheckins SET venue_id='{$enc_venue_id}' WHERE venue_id='{$enc_foursquare_id}'";

		foreach ($GLOBALS['cfg']['db_users']['host'] as $cluster_id => $ignore){
			db_write_users($cluster_id, $sql);
		}

	}

	$sql = "SELECT * FROM FoursquareVenues";
	backfill_db_users($sql, "migrate_venue");

	exit();

?>
