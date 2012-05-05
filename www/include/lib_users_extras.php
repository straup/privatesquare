<?php

	# What it says on the tin: per-user extras (like non-default preferences)

	#################################################################

	function users_extras_for_user(&$user){

		if (! users_extras_ensure_extras($user)){
			return not_okay("failed to create user extras");
		}

		return users_extras_get_for_user($user);
	}

	# See what's going on here? It's a bit squirrel-y but a) it's not
	# the end of the world and b) it allows us to populate the users_extras
	# table lazily (in the users_extras_ensure_extras function below) for
	# people who are running older versions of flamework (20120505/straup)

	function users_extras_get_for_user(&$user){

		$cache_key = "users_extras_{$user['id']}";
		$cache = cache_get($cache_key);

		if ($cache['ok']){
			return $cache['data'];
		}

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		$sql = "SELECT * FROM users_extras WHERE user_id='{$enc_user}'";
		$rsp = db_fetch_users($cluster_id, $sql);

		if (! $rsp['ok']){
			return $rsp;
		}

		$extras = db_single($rsp);

		$rsp = okay(array(
			'extras' => $extras
		));

		cache_set($cache_key, $rsp, "cache locally");

		return $rsp;
	}

	#################################################################

	function users_extras_update(&$user, $extras){

		if (! users_extras_ensure_extras($user)){
			return not_okay("failed to create user extras");
		}

		$cluster_id = $user['cluster_id'];
		$enc_user = AddSlashes($user['id']);

		$update = array();

		foreach ($extras as $k => $v){
			$update[$k] = AddSlashes($v);
		}

		$where = "user_id='{$enc_user}'";

		$rsp = db_update_users($cluster_id, 'users_extras', $update, $where);

		if ($rsp['ok']){
			$cache_key = "users_extras_{$user['id']}";
			cache_unset($cache_key);
		}

		return $rsp;
	}

	#################################################################

	# Helper code to account for users who are using an older version
	# of flamework (20120505/straup)

	function users_extras_ensure_extras(&$user){

		$rsp = users_extras_get_for_user($user);
		$ok = ($rsp['extras']) ? 1 : 0;

		if (! $ok){

			$cluster_id = $user['cluster_id'];

			$insert = array(
				'user_id' => AddSlashes($user['id']),
			);

			$rsp = db_insert_users($cluster_id, 'users_extras', $insert);
			$ok = $rsp['ok'];
		}

		return $ok;
	}

	#################################################################
?>
