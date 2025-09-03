<?php

	# https://tools.ietf.org/html/rfc6749#section-4.1
	# https://tools.ietf.org/html/rfc6749#section-4.1.3
	# https://tools.ietf.org/html/rfc6749#section-4.1.4
	
	# please make sure to cover all these bases
	# https://maxfieldchen.com/posts/2020-05-17-penetration-testers-guide-oauth-2.html
	
	include("include/init.php");

	features_ensure_enabled(array(
		"api",
		"api_www",
		"api_delegated_auth",
	));

	loadlib("api_keys");
	loadlib("api_keys_utils");

	loadlib("api_oauth2_grant_tokens");
	loadlib("api_oauth2_access_tokens");

	#

	function local_send_json($json, $is_error=0){

		if ((! $is_error) && (isset($json['error']))){
			$is_error = 1;
		}

		if ($is_error){
			header("HTTP/1.1 400 Bad Request");
		}

		header("Content-Type: application/json;charset=UTF-8");
		header("Cache-Control: no-store");
		header("Pragma: no-cache");

		# error_log("[API] " . var_export($json, 1));

		echo json_encode($json);
		exit();
	}

	#

	$key_more = array(
		'ensure_isown' => 0
	);

	$key_row = api_keys_utils_get_from_url($key_more);
	$GLOBALS['smarty']->assign("key", $key_row);

	$ok = 1;
	$error = null;
	$error_description = null;
	
	# Basics (redirect URLs)

	if (($ok) && (! $key_row['app_callback'])){
		error_403();
	}

	# Basics (everything else)

	$grant = request_str("grant_type");
	$code = request_str("code");

	$redirect_uri = request_str("redirect_uri");

	# we check for and test 'client_id' in api_keys_utils_get_from_url()
	
	if ((! $code) || (! $grant) || (! $redirect_uri)){
		$error = "invalid_request";
		$error_description = "One or more required parameters is missing";		       
		$ok = 0;
	}

	if (($ok) && ($grant != "authorization_code")){
		$error = "invalid_grant";
		$error_description = "Invalid grant type";
		$ok = 0;	
	}

	if (($ok) && ($key_row["app_callback"] != $redirect_uri)){
		$error = "invalid_request";
		$error_description = "Invalid redirect URI";		
		$ok = 0;
	}
	
	if (! $ok){

 		$rsp = array(
		     'error' => $error,
		     'error_description' => $error_description,
		);

		local_send_json($rsp);
		exit();
	}

	# Sort out the grant tokens

	$grant_token = api_oauth2_grant_tokens_get_by_code($code);

	if (($ok) && (! $grant_token)){
		$error = "access_denied";
		$error_description = "Invalid grant token (1)";
		$ok = 0;
	}

	if (($ok) && ($grant_token['code'] != $code)){
		$error = "access_denied";
		$error_description = "Invalid grant token (2)";
		$ok = 0;
	}

	if (($ok) && ($grant_token['api_key_id'] != $key_row['id'])){
		$error = "access_denied";
		$error_description = "Invalid grant token (4)";
		$ok = 0;
	}

	if (($ok) && (! api_oauth2_grant_tokens_is_timely($grant_token))){
		$error = "access_denied";
		$error_description = "Invalid grant token (4)";	
		$ok = 0;
	}

	if ($ok){

		$user = users_get_by_id($grant_token['user_id']);

		if ((! $user) || ($user['deleted'])){
			$error = "access_denied";
			$error_description = "Invalid grant token (4)";	
			$ok = 0;
		}		
	}

	if (! $ok){

 		$rsp = array(
		     'error' => $error,
		     'error_description' => $error_description,
		);
		
		local_send_json($rsp);
		exit();
	}

	# Purge the grant

	api_oauth2_grant_tokens_delete($grant_token);

	# Generate the access token (check to make sure one doesn't already exist)

	$access_token = api_oauth2_access_tokens_get_for_user_and_key($user, $key_row);

	if (! $access_token){

		$perms = $grant_token['perms'];
		$ttl = $grant_token['ttl'];

		$rsp = api_oauth2_access_tokens_create($key_row, $user, $perms, $ttl);

		if (! $rsp['ok']){

			$rsp = array('error' => 'server_error');
			local_send_json($rsp);
			exit();
		}

		$access_token = $rsp['token'];		
	}

	# Okay, soup for you!

	$perms_map = api_oauth2_access_tokens_permissions_map();
	$scope = $perms_map[$access_token['perms']];

	$expires = $access_token['expires'];
	$expires_in = $expires - time();

	$rsp = array(
		'access_token' => $access_token['access_token'],
		'token_type' => 'bearer',
		'scope' => $scope,
		'expires' => $expires,
		'expires_in' => $expires_in,
	);

	local_send_json($rsp);
	exit();
?>
