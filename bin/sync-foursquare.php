<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");

	loadlib("foursquare_api");
	loadlib("foursquare_venues");
	loadlib("privatesquare_checkins");
	loadlib("backfill");

	function sync_user($fsq_user, $more=array()){

		$user = users_get_by_id($fsq_user['user_id']);

		if (! $user['sync_foursquare']){
			echo "'{$user['username']}' has not opted in to foursquare syncing, skipping...\n";
			return;
		}

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
					continue;
				}

				$checkin = array(
					'user_id' => $user['id'],
					'checkin_id' => $fsq_checkin['id'],
					'venue_id' => $fsq_checkin['venue']['id'],
					'created' => $fsq_checkin['createdAt'],
					'status_id' => $status_map['i am here'],
				);

				$venue = foursquare_venues_get_by_venue_id($checkin['venue_id']);

				if (! $venue){
					$rsp = foursquare_venues_archive_venue($checkin['venue_id']);

					if (! $rsp['ok']){
						echo "failed to archive venue '{$checkin['venue_id']}' : {$rsp['error']}\n";
						echo "skipping...\n";
						continue;
					}

					$venue = $rsp['venue'];
				}

				if ($venue){
					$checkin['locality'] = $venue['locality'];
				}

				$rsp = privatesquare_checkins_create($checkin);

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

	$sql = "SELECT * FROM FoursquareUsers";
	backfill_db_users($sql, "sync_user");

	exit();
?>
