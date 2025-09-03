<?php

	#################################################################

	#
	# create a user record. the fields pass in $user
	# ARE NOT ESCAPED.
	#

	function flamework_users_create_user($user){

		#
		# set up some extra fields first
		#

		loadlib('random');

		$user['password'] = passwords_encrypt_password($user['password']);
		$user['created'] = time();
		$user['conf_code'] = random_string(24);

		$user['cluster_id'] = flamework_users_assign_cluster_id();


		#
		# now create the escaped version
		#

		$hash = array();
		foreach ($user as $k => $v){
			$hash[$k] = AddSlashes($v);
		}

		$ret = db_insert_accounts('users', $hash);

		if (!$ret['ok']) return $ret;


		#
		# cache the unescaped version
		#

		$user['id'] = $ret['insert_id'];

		cache_set("USER-{$user['id']}", $user);

		return array(
			'ok'	=> 1,
			'user'	=> $user,
		);
	}

	#################################################################

	#
	# update multiple fields on an user record. the hash passed
	# in $update IS NOT ESCAPED.
	#

	function flamework_users_update_user($user, $update){

		$hash = array();

		foreach ($update as $k => $v){
			$hash[$k] = AddSlashes($v);
		}

		$ret = db_update_accounts('users', $hash, "id={$user['id']}");

		if (!$ret['ok']) return $ret;

		cache_unset("USER-{$user['id']}");

		return array(
			'ok' => 1,
		);
	}

	#################################################################

	function flamework_users_update_password(&$user, $new_password){

		$enc_password = passwords_encrypt_password($new_password);

		return flamework_users_update_user($user, array(
			'password' => AddSlashes($enc_password),
		));
	}

	#################################################################

	function flamework_users_delete_user($user){

		$ts = time();
		
		return flamework_users_update_user($user, array(
			'deleted'	=> time(),
			'email'		=> $user['email'] . ".DELETED-{$ts}",

			# reset the password here ?
		));
	}

	#################################################################

	function flamework_users_reload_user(&$user){

		$user = flamework_users_get_by_id($user['id']);
	}

	#################################################################

	function flamework_users_get_by_id($id){

		$sql = "SELECT * FROM users WHERE id=" . intval($id);

		$rsp = db_fetch_accounts($sql);
		$user = db_single($rsp);

		cache_set("USER-{$user['id']}", $user);

		return $user;
	}

	#################################################################

	function flamework_users_get_by_email($email){

		$enc_email = AddSlashes($email);
		$sql = "SELECT * FROM users WHERE email='{$enc_email}'";

		$rsp = db_fetch_accounts($sql);
		return db_single($rsp);
	}

	#################################################################

	function flamework_users_get_by_login($email, $password){

		$user = flamework_users_get_by_email($email);

		if (!$user){
			return null;
		}

		if ($user['deleted']){
			return null;
		}

		if (! passwords_utils_validate_password_for_user($password, $user)){
			return null;
		}

		return $user;
	}

	#################################################################

	function flamework_users_is_email_taken($email){

		$enc_email = AddSlashes($email);
		$sql = "SELECT id FROM users WHERE email='{$enc_email}' AND deleted=0";

		$rsp = db_fetch_accounts($sql);
		$row = db_single($rsp);

		return (($row) && (array_key_exists('id', $row))) ? 1 : 0;
	}

	#################################################################

	function flamework_users_is_username_taken($username){

		$enc_username = AddSlashes($username);

		$sql = "SELECT id FROM users WHERE username='{$enc_username}' AND deleted=0";
		$rsp = db_fetch_accounts($sql);

		$row = db_single($rsp);
		return (($row) && (array_key_exists('id', $row))) ? 1 : 0;		
	}

	#################################################################

	function flamework_users_assign_cluster_id(){

		# TO DO: an actual cluster ID if federated

		return 1;
	}

	#################################################################
