<?php

	# THIS HAS NOT BEEN TESTED

	#################################################################

	function users_preferences_defaults(){

		if (! isset($GLOBALS['cfg']['users_preferences_defaults'])){
			return array();
		}

		$GLOBALS['cfg']['users_preferences_defaults'];
	}

	#################################################################

	function users_preferences_for_user(&$user){

		$defaults = users_preferences_defaults();
		$prefs = array();

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

	function users_preferences_reset_for_user($user){

		$cluster_id = $user['cluster_id'];

		$enc_id = AddSlashes($user['id']);
		$sql = "DELETE FROM UsersPreferences WHERE user_id='{$enc_id}'";

		db_write_users($cluster_id, $sql);
	}

	#################################################################
?>
