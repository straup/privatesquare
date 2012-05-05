<?php

	#################################################################

	loadlib("users_extras");

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

		# TO DO: error checking/handling on $rsp here...

		$rsp = users_extras_for_user($user);
		$extras = $rsp['extras'];

		if (! $extras['preferences']){
			return $prefs;
		}

		$user_prefs = json_decode($extras['preferences'], 'as hash');

		if (! $user_prefs){
			return $prefs;
		}

		$prefs = array_merge($defaults, $user_prefs);

		return $prefs;
	}

	#################################################################

	function users_preferences_assign(&$user, $prefs){

		$defaults = users_preferences_defaults();
		$new = array();

		foreach ($defaults as $k => $v){

			if (! isset($prefs[$k])){
				continue;
			}

			if ($v == $prefs[$k]){
				continue;
			}

			$new[$k] = $prefs[$k];
		}

		$new = (count($new)) ? json_encode($new) : '';

		$update = array(
			'preferences' => $new,
		);

		$rsp = users_extras_update($user, $update);

		if ($rsp['ok']){
			$rsp['preferences'] = users_preferences_for_user($user);
		}

		return $rsp;
	}

	#################################################################

	function users_preferences_reset(&$user){

		$update = array(
			'preferences' => '',
		);

		return users_extras_update($user, $update);
	}

	#################################################################

?>
