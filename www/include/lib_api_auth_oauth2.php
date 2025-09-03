<?php

	loadlib("api_oauth2_access_tokens");

	#################################################################

	function api_auth_oauth2_get_access_token($method){

		# https://tools.ietf.org/html/draft-ietf-oauth-v2-bearer-20#section-2.1

		$require_header = $GLOBALS['cfg']['api_oauth2_require_authentication_header'];
		$check_header = $GLOBALS['cfg']['api_oauth2_check_authentication_header'];

		if (($require_header) || ($check_header)){

			$headers = apache_request_headers();
			$token = null;
			$auth = null;

			# sigh, PHP...

			if (isset($headers['Authorization'])){
				$auth = $headers['Authorization'];
			}

			else if (isset($headers['authorization'])){
				$auth = $headers['authorization'];
			}

			else {}

			if (! $auth){

				if ($require_header){
					return null;
				}
			}

			else {

				if (preg_match("/Bearer\s+([a-zA-Z0-9\+\/\=]+)$/", $auth, $m)){

					$token = $m[1];
					$token = base64_decode($token);
				}
			}

			if (($token) || ($require_header)){
				return $token;
			}
		}

		if ($GLOBALS['cfg']['api_oauth2_allow_get_parameters']){
			return request_str('access_token');
		}

		return post_str('access_token');
	}

	#################################################################

	function api_auth_oauth2_has_auth($method, $key_row=null){

		$access_token = api_auth_oauth2_get_access_token($method);

		if (! $access_token){
			return array('ok' => 0, 'error_code' => 494);
		}

		/*
		$token_row = api_oauth2_access_tokens_get_by_token($access_token);

		if (! $token_row){
			return array('ok' => 0, 'error_code' => 493);
		}

		if ($token_row['disabled']){
			return array('ok' => 0, 'error_code' => 492);
		}

		if (($token_row['expires']) && ($token_row['expires'] < time())){
			return array('ok' => 0, 'error_code' => 491);
		}

		# I find it singularly annoying that we have to do this here
		# but OAuth gets what [redacted] wants. See also: notes in
		# lib_api.php around ln 65 (20121026/straup)

		$key_row = api_keys_get_by_id($token_row['api_key_id']);
		$rsp = api_keys_utils_is_valid_key($key_row);

		if (! $rsp['ok']){
			return $rsp;
		}
		*/

		$token_row = null;
		$key_row = null;

		if (preg_match('/^st-/', $access_token)){

			$token_row = api_site_tokens_get_access_token_for_token($access_token);

			if (! $token_row){
				return array('ok' => 0, 'error_code' => 493);
			}

			$key_row = api_site_tokens_get_api_key_by_id($token_row['api_key_id']);

		} else {

			$token_row = api_oauth2_access_tokens_get_by_token($access_token);
			$key_row = api_keys_get_by_id($token_row['api_key_id']);
		}

		// START OF validate token and key

		if (! $token_row){
			return array('ok' => 0, 'error_code' => 493);
		}

		api_log(array(
			"access_token_id" => $token_row["id"],
			"access_token_user_id" => $token_row["user_id"]			
		));

		if ($key_row){
			api_log(array(
				"api_key_id" => $key_row["id"],
				"api_key_user_id" => $key_row["user_id"],
				"api_key_role_id" => $key_row["role_id"],				
			));
		}
		
		if ($token_row['disabled']){
			return array('ok' => 0, 'error_code' => 492);
		}

		if (($token_row['expires']) && ($token_row['expires'] < time())){
			return array('ok' => 0, 'error_code' => 491);
		}

		$rsp = api_keys_utils_is_valid_key($key_row);

		if (! $rsp['ok']){
			return $rsp;
		}

		// END OF validate token and key

		if (isset($method['requires_perms'])){

			if ($token_row['perms'] < $method['requires_perms']){
				$perms_map = api_oauth2_access_tokens_permissions_map();
				$required = $perms_map[$method['requires_perms']];
				return array('ok' => 0, 'error_code' => 490);
			}
		}

		# Ensure user-iness - this may seem like a no-brainer until you think
		# about how the site itself uses the API in the absence of a logged-in
		# user (20130508/straup)

		$ensure_user = 1;
		$user = null;

		if ((! $token_row['user_id']) && ($key_row) && (features_is_enabled("api_oauth2_tokens_null_users"))){

			$key_role_id = $key_row['role_id'];
			$roles_map = api_keys_roles_map('string keys');

			$valid_roles = $GLOBALS['cfg']['api_oauth2_tokens_null_users_allowed_roles'];
			$valid_roles_ids = array();

			foreach ($valid_roles as $role){
				$valid_roles_ids[] = $roles_map[$role];
			}

			$ensure_user = (($key_role_id) && (in_array($key_role_id, $valid_roles_ids))) ? 0 : 1;
		}

		if ($ensure_user){

			$user = users_get_by_id($token_row['user_id']);

			if ((! $user) || ($user['deleted'])){
				return array('ok' => 0, 'error_code' => 460);
			}

		}

		# Staff checks are handled in lib_api_sfomuseum_staff.php
		
		return array(
			'ok' => 1,
			'access_token' => $token_row,
			'api_key' => $key_row,
			'user' => $user,
		);
	}

	#################################################################

	# the end
