<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");

	loadlib("cli");
	loadlib("foursquare_api");
	loadlib("privatesquare_checkins");
	loadlib("venues_providers");
	loadlib("venues_geo");
	loadlib("backfill");

	function sync_user($fsq_user, $more=array()){

		$user = users_get_by_id($fsq_user['user_id']);

		if (! $user['sync_foursquare']){
			echo "'{$user['username']}' has not opted in to foursquare syncing, skipping...\n";
			return;
		}

		$provider_id = venues_providers_label_to_id("foursquare");

		echo "sync checkins for '{$user['username']}' : {$user['sync_foursquare']}\n";

		$status_map = privatesquare_checkins_status_map("string keys");

		$method = 'users/self/checkins';

		$count = null;
		$offset = 0;
		$limit = 250;

		while ((! isset($count)) || ($offset < $count)){

			$args = array(
				'oauth_token' => $fsq_user['oauth_token'],
				'limit' => $limit,
				'offset' => $offset,
			);

			# only sync updates since the user signed up for privatesquare
			# > 1 (or "2") would mean pull in all a users' checkins.

			# see also: account_foursquare_sync.php

			if ($user['sync_foursquare'] == 1){
				$args['afterTimestamp'] = $user['created'];
			}

			$rsp = foursquare_api_call($method, $args);

			if (! isset($count)){
				$count = $rsp['rsp']['checkins']['count'];
			}

			$count_items = count($rsp['rsp']['checkins']['items']);

			# As of 20120218 if you pass a date filter to the API it
			# still returns the count for the total number of checkins
			# without the date filter. I love that... (20120218/straup)

			if (! $count_items){
				break;
			}

			foreach ($rsp['rsp']['checkins']['items'] as $fsq_checkin){

				if (privatesquare_checkins_get_by_foursquare_id($user, $fsq_checkin['id'])){
					echo "already checked in ({$fsq_checkin['id']})\n";
					continue;
				}

				$fsq_venue_id = $fsq_checkin['venue']['id'];
				$venue = venues_get_by_venue_id_for_provider($fsq_venue_id, $provider_id);

				if (! $venue){

					$rsp = venues_archive_venue_for_provider($fsq_venue_id, $provider_id);

					if ($rsp['ok']){
						$venue = $rsp['venue'];
					}
				}

				if (! $venue){
					echo "Failed to retrieve venue '{$fsq_venue_id}' because: {$rsp['error']}\n";
					continue;
				}

				echo "checkin to {$venue['name']}\n";

				$checkin = array(
					'user_id' => $user['id'],
					'checkin_id' => $fsq_checkin['id'],
					'venue_id' => $venue['venue_id'],
					'created' => $fsq_checkin['createdAt'],
					'status_id' => $status_map['i am here'],
					'latitude' => $fsq_checkin['venue']['location']['lat'],
					'longitude' => $fsq_checkin['venue']['location']['lng'],
				);

				venues_geo_append_hierarchy($checkin['latitude'], $checkin['longitude'], $checkin);

				# echo json_encode($checkin) . "\n";

				$rsp = privatesquare_checkins_create($checkin);

				# dumper($rsp);
				# exit();

				if (! $rsp['ok']){
					echo "failed to archive checkin: {$rsp['error']}\n";
					continue;
				}

				echo "archived 4sq checkin {$checkin['checkin_id']} with privatesquare ID: {$rsp['checkin']['id']}\n";
			}

			# do stuff here...

			$offset += $limit;
		}
	}

	# Go!

	$spec = array(
	 	"u" => array("name" => "user_id", "required" => 0, "help" => "Sync foursquare checkins for a specific user ID"),
	 	"f" => array("name" => "foursquare_id", "required" => 0, "help" => "Sync foursquare checkins for a specific foursquare user ID (not username)"),
	);

	$opts = cli_getopts($spec);

	$sql = "SELECT * FROM FoursquareUsers";

	if ($id = $opts['user_id']){
		$enc_id = AddSlashes($id);
		$sql .= " WHERE user_id='{$enc_id}'";
	}

	else if ($id = $opts['foursquare_id']){
		$enc_id = AddSlashes($id);
		$sql .= " WHERE foursquare_id='{$enc_id}'";
	}

	else {}

	backfill_db_users($sql, "sync_user");
	exit();
?>
