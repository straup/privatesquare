<?php

	#################################################################

	function users_create_user($user){
		$provider = users_get_provider();
		$func = "{$provider}_users_create_user";
		return call_user_func($func, $user);
	}

	#################################################################

	function users_update_user(&$user, $update){
		$provider = users_get_provider();
		$func = "{$provider}_users_update_user";
		return call_user_func($func, $user, $update);
	}

	#################################################################

	function users_update_password(&$user, $new_password){

		$enc_password = passwords_encrypt_password($new_password);

		return users_update_user($user, array(
			'password' => AddSlashes($enc_password),
		));
	}

	#################################################################

	function users_delete_user($user){
		$provider = users_get_provider();
		$func = "{$provider}_users_delete_user";
		return call_user_func($func, $user);
	}

	#################################################################

	function users_get_by_id($id){
		$provider = users_get_provider();
		$func = "{$provider}_users_get_by_id";
		return call_user_func($func, $id);
	}

	#################################################################

	function users_get_by_email($email){
		$provider = users_get_provider();
		$func = "{$provider}_users_get_by_email";
		return call_user_func($func, $email);
	}

	#################################################################

	function users_get_by_login($email, $password){
		$provider = users_get_provider();
		$func = "{$provider}_users_get_by_login";
		return call_user_func($func, $email, $password);
	}

	#################################################################

	function users_is_email_taken($email){
		$provider = users_get_provider();
		$func = "{$provider}_users_is_email_taken";
		return call_user_func($func, $email);
	}

	#################################################################

	function users_is_username_taken($username){
		$provider = users_get_provider();
		$func = "{$provider}_users_is_username_taken";
		return call_user_func($func, $username);
	}

	#################################################################

	function users_get_provider(){

		$provider = $GLOBALS['cfg']['users_use_module'];
		
		if (! preg_match("/^[a-z_]+$/", $provider)){
			die("Missing or invalid users provider.");
		}

		$provider_lib = "{$provider}_users";
		loadlib($provider_lib);

		return $provider;
	}

	#################################################################

	# the end