<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");
	loadlib("backfill");
	loadlib("venues_geo");

	# Basically this moves all the venues listed in the FoursquareVenues table and
	# moves them into the Venues table assigning a provider ID (foursquare) and 
	# creating an artisanal venue ID. It will also update the PrivatesquareCheckins
	# table to point to the newly created venue ID.
	#
	# Depending on where you are running this you may need to update Venues and checkins
	# separately. You can update all the checkins to point to the new Venues (once they've
	# been created) with a single SQL statement, as is:
	#
	# UPDATE PrivatesquareCheckins c, Venues v SET c.venue_id = v.venue_id WHERE c.venue_id=v.provider_venue_id;
	#
	# (20131124/straup)

	function migrate_venue($row){

		$venue = $row;

		$venue['provider_id'] = venues_providers_label_to_id("foursquare");
		$venue['provider_venue_id'] = $row['venue_id'];

		if ((isset($venue['latitude'])) && (isset($venue['longitude']))){
			venues_geo_append_hierarchy($venue['latitude'], $venue['longitude'], $venue);
		}

		$rsp = venues_add_venue($venue);

		if (! $rsp['ok']){
			dumper($rsp);
			exit;
		}

		$venue_id = $rsp['venue']['venue_id'];
		$foursquare_id = $rsp['venue']['provider_venue_id'];

		echo "{$foursquare_id} : {$venue_id}\n";

		# See above
		# return;

		$enc_venue_id = AddSlashes($venue_id);
		$enc_foursquare_id = AddSlashes($foursquare_id);

		$sql = "UPDATE PrivatesquareCheckins SET venue_id='{$enc_venue_id}' WHERE venue_id='{$enc_foursquare_id}'";

		foreach ($GLOBALS['cfg']['db_users']['host'] as $cluster_id => $ignore){
			$rsp = db_write_users($cluster_id, $sql);
		}

	}

	$more = array('per_page' => 100);

	$sql = "SELECT * FROM FoursquareVenues";
	backfill_db_users($sql, "migrate_venue", $more);

	exit();

?>
