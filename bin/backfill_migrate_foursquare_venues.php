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

		# $rsp = venues_add_venue($venue);
		# return $rsp;
	}

	$sql = "SELECT * FROM FoursquareVenues";
	backfill_db_users($sql, "migrate_venue");

	exit();

?>
