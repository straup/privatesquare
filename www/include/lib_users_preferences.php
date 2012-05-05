<?php

	# THIS HAS NOT BEEN TESTED

	#################################################################

	function users_preferences_defaults(){

		if (! isset($GLOBALS['cfg']['users_preferences_defaults'])){
			return array();
		}

		return $GLOBALS['cfg']['users_preferences_defaults'];
	}

	#################################################################

	function users_preferences_for_user(&$user){

		$defaults = users_preferences_defaults();
		$prefs = $defaults;

		$cluster_id = $user['cluster_id'];

		$enc_id = AddSlashes($user['id']);
		$sql = "SELECT * FROM UsersPreferences WHERE user_id='{$enc_id}'";

		$rsp = db_fetch_users($cluster_id, $sql);

		if (! $rsp['ok']){
			return $rsp;
		}

		foreach ($rsp['rows'] as $row){
	
			$pref = $row['pref'];
			$value = $row['value'];

			if (! isset($defaults[$key])){
				continue;
			}

			$prefs[$pref] = $value;
		}

		return okay(array(
			'preferences' => $prefs
		));
	}

	#################################################################

	function users_preferences_reset(&$user){

		$cluster_id = $user['cluster_id'];

		$enc_id = AddSlashes($user['id']);
		$sql = "DELETE FROM UsersPreferences WHERE user_id='{$enc_id}'";

		db_write_users($cluster_id, $sql);
	}

	#################################################################

	function users_preferences_update(&$user, $prefs){

		$defaults = users_preferences_defaults();
		$new = array();

		foreach ($defaults as $k => $v){

			if (! isset($prefs[$k])){
				continue;
			}

			if ($v == $prefs[$k]){
				continue;
			}

			$new[$k] = $v;
		}

		if (! count($new)){

			return okay(array(
				'preferences' => $prefs,
			));
		}

		$rsp = users_preferences_reset($user);

		if (! $rsp['ok']){
			return $rsp;
		}

		$cluster_id = $user['cluster_id'];

		foreach ($new as $k => $v){

			$insert = array(
				'user_id' => AddSlashes($user['id']),
				'preference' => AddSlashes($k),
				'value' => AddSlashes($v),
			);

			db_insert_users('UsersPreferences', $insert, $cluster_id);
		}

		return users_preferences_for_user($user);
	}

	#################################################################

?>
