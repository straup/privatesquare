<?php

	# https://tools.ietf.org/html/rfc6749#section-4.1
	# https://tools.ietf.org/html/rfc6749#section-4.1.1
	# https://tools.ietf.org/html/rfc6749#section-4.1.2
	
	# please make sure to cover all these bases
	# https://maxfieldchen.com/posts/2020-05-17-penetration-testers-guide-oauth-2.html

	include("include/init.php");

	features_ensure_enabled(array(
		"api",
		"api_www", 
		"api_delegated_auth"
	));

	login_ensure_loggedin();

	loadlib("api_keys");
	loadlib("api_keys_utils");
	loadlib("api_oauth2_access_tokens");
	loadlib("api_oauth2_grant_tokens");

	$key_more = array(
		'ensure_isown' => 0
	);

	$key_row = api_keys_utils_get_from_url($key_more);
	$GLOBALS['smarty']->assign("key", $key_row);

	$crumb_key = 'access_token_register';
	$GLOBALS['smarty']->assign("crumb_key", $crumb_key);

	$perms_map = api_oauth2_access_tokens_permissions_map();
	$GLOBALS['smarty']->assign("permissions", $perms_map);

	$ttl_map = api_oauth2_access_tokens_ttl_map();
	$GLOBALS['smarty']->assign("ttl_map", $ttl_map);

	# Handy helper mode to create auth tokens for yourself...

	if (request_isset("self")){

		features_ensure_enabled("api_authenticate_self");

		if ($key_row['user_id'] != $GLOBALS['cfg']['user']['id']){
			error_403();
		}

		if ($token_row = api_oauth2_access_tokens_get_for_user_and_key($GLOBALS['cfg']['user'], $key_row)){
			$GLOBALS['smarty']->assign("token_row", $token_row);
			$GLOBALS['smarty']->assign("has_token", 1);
		}

		else if ((post_isset("confirm")) && (crumb_check($crumb_key))){

			$perms = request_str("perms");
			$ttl = request_int32("ttl");

			if (! api_oauth2_access_tokens_is_valid_permission($perms)){
				$GLOBALS['smarty']->assign("error", "bad_perms");
			}

			else {
				$rsp = api_oauth2_access_tokens_create($key_row, $GLOBALS['cfg']['user'], $perms, $ttl);
				$GLOBALS['smarty']->assign("token_rsp", $rsp);
			}
		}

		else {}

		$GLOBALS['smarty']->display("page_api_oauth2_authenticate_self.txt");
		exit();
	}

	# Okay, let's do this

	$ok = 1;

	# we check for and test 'client_id' in api_keys_utils_get_from_url()
	
	$scope = request_str("scope");

	if (($ok) && (! api_oauth2_access_tokens_is_valid_permission($scope, "string perms"))){
		$GLOBALS['smarty']->assign("error", "invalid_scope");
		$ok = 0;
	}

	if (($ok) && (request_str("redirect_uri") != $key_row['app_callback'])){
		$GLOBALS['smarty']->assign("error", "invalid_callback");
		$ok = 0;
	}

	if (($ok) && (request_str("response_type") != "code")){
		$GLOBALS['smarty']->assign("error", "invalid_type");
		$ok = 0;
	}

	$state = request_str("state");

	if ($state == ""){
		$GLOBALS['smarty']->assign("error", "missing_state");
		$ok = 0;
	}

	$GLOBALS['smarty']->assign('state', $state);
	
	# Do we already have a grant token for this user?

	# And yes this is a repeat of the code below that should maybe be
	# moved in to a function or something. But for now it's fine...
	# (20121024/straup)

	if (($ok) && ($token = api_oauth2_grant_tokens_get_for_user_and_key($GLOBALS['cfg']['user'], $key_row))){

		if (api_oauth2_grant_tokens_is_timely($token)){

			$rsp_params = array(
				'code' => $token['code'],
				'state' => $state,
			);

			$rsp_params = http_build_query($rsp_params);

			$url = $key_row['app_callback'] . "?" . $rsp_params;

			header("location: {$url}");
			exit();
		}

		else {
			api_oauth2_grant_tokens_delete($token);
		}
	}

	# Do we already have an access token (with the same perms) for this user?

	if (($ok) && ($token_row = api_oauth2_access_tokens_get_for_user_and_key($GLOBALS['cfg']['user'], $key_row))){

		$perms_map = api_oauth2_access_tokens_permissions_map("string keys");
		$perms = $perms_map[$scope];

		# If we do just automagically create a stub grant so that the app
		# can fetch the token (again – maybe they are doing some kind of
		# SSO) by calling the /access_token endpoint

		if ($perms == $token_row['perms']){

			$rsp = api_oauth2_grant_tokens_create($key_row, $GLOBALS['cfg']['user'], $perms);

			$rsp_params = array(
				'state' => $state,
			);

			if (! $rsp['ok']){
				$rsp_param['error'] = 'server_error';
			}

			else {
				$rsp_params['code'] = $rsp['token']['code'];
			}

			$rsp_params = http_build_query($rsp_params);

			$url = $key_row['app_callback'] . "?" . $rsp_params;

			header("location: {$url}");
			exit();
		}
	}

	# Make it go!

	if (($ok) && (post_isset('done')) && (crumb_check($crumb_key))){

		$rsp_params = array(
			'state' => $state,
		);

		if (post_str("confirm") == "YES, I AGREE"){

			$perms_map = api_oauth2_access_tokens_permissions_map("string keys");
			$perms = $perms_map[$scope];

			$ttl = post_int32("ttl");

			# create grant token 

			$rsp = api_oauth2_grant_tokens_create($key_row, $GLOBALS['cfg']['user'], $perms, $ttl);

			if (! $rsp['ok']){
				$rsp_param['error'] = 'server_error';
			}

			else {
				$rsp_params['code'] = $rsp['token']['code'];
			}
		}

		else {
			$rsp_params['error'] = 'access_denied';
		}


		$rsp_params = http_build_query($rsp_params);

		$url = $key_row['app_callback'] . "?" . $rsp_params;

		header("location: {$url}");
		exit();
	}

	if ($ok){
		$GLOBALS['smarty']->assign("str_perms", $scope);
	}

	$GLOBALS['smarty']->display("page_api_oauth2_authenticate.txt");
	exit();

?>
