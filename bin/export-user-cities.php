<?php

	$root = dirname(dirname(__FILE__));
	ini_set("include_path", "{$root}/www:{$root}/www/include");

	set_time_limit(0);

	include("include/init.php");

	loadlib("backfill");
	loadlib("cli");

	loadlib("privatesquare_checkins");
	loadlib("privatesquare_export_geojson");
	loadlib("privatesquare_export_csv");

	function export_user_cities($user, $more=array()){

		$enc_user = AddSlashes($user['id']);
		$cluster_id = $user['cluster_id'];

		$sql = "SELECT locality, COUNT(id) AS count FROM PrivatesquareCheckins WHERE user_id='{$enc_user}' AND locality != 0 GROUP BY locality";
		$rsp = db_fetch_users($cluster_id, $sql);

		foreach ($rsp['rows'] as $row){

			$locality = $row['locality'];

			echo "[{$user['id']}] fetch checkins for {$locality}\n";

			$page_count = 0;

			$more = array(
				'locality' => $locality,
				'per_page' => 1000,
				'page' => 0,
			);

			$checkins = array();

			while ((! $page_count) || ($page_count > $more['page'])){

				$more['page'] += 1;

				# This is not awesome for people with a gazillion checkins
				# in a single city. Work should be done to work out some
				# sort of streaming hoo-hah to compensate for that...
				# (20120514/straup)

				$rsp_ch = privatesquare_checkins_for_user($user, $more);
				$checkins = array_merge($checkins, $rsp_ch['rows']);

				if (! $page_count){
					$page_count = $rsp_ch['pagination']['page_count'];
				}
			}

			echo "[{$user['id']}] " . count($checkins) . " checkins in {$locality}\n";

			# sudo put me in a function...

			$root = "{$GLOBALS['cfg']['export_static_path']}{$user['id']}/locality/";

			if (! is_dir($root)){
				mkdir($root, 0755, "recursive");
			}

			$path_geojson = $root . "{$locality}.geojson";
			$path_csv = $root . "{$locality}.csv";

			$more = array(
				'donot_send' => 1
			);

			$fh = fopen($path_geojson, "w");
			privatesquare_export_geojson($fh, $checkins, $more);
			fclose($fh);

			echo "[{$user['id']}] wrote {$path_geojson}: {$rsp['ok']}\n";

			$fh = fopen($path_csv, "w");
			$rsp = privatesquare_export_csv($fh, $checkins, $more);
			fclose($fh);

			echo "[{$user['id']}] wrote {$path_csv}: {$rsp['ok']}\n";
		}
	}

	# main()

	$spec = array(
		'u' => array('name' => 'user', 'required' => 0, 'help' => 'Export cities for a specific user (ID)'),
	);

	$opts = cli_getopts($spec);

	if ($opts['u']){
		$user = users_get_by_id($opts['u']);

		if (! $user){
			echo "Invalid user ID\n";
			exit();
		}

		export_user_cities($user);
	}

	else {
		$sql = "SELECT * FROM users";
		backfill_db_users($sql, "export_user_cities");
	}

	exit();

?>
