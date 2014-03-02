<?php

	include("include/init.php");

	loadlib("http");
	loadlib("random");
	loadlib("foursquare_api");
	loadlib("foursquare_users");

	# Some basic sanity checking like are you already logged in?

	if ($GLOBALS['cfg']['user']['id']){
		header("location: {$GLOBALS['cfg']['abs_root_url']}");
		exit();
	}


	if (! $GLOBALS['cfg']['enable_feature_signin']){
		$GLOBALS['smarty']->display("page_signin_disabled.txt");
		exit();
	}

	$code = get_str("code");

	if (! $code){
		error_404();
	}

	$rsp = foursquare_api_get_auth_token($code);

	if (! $rsp['ok']){
		$GLOBALS['error']['oauth_access_token'] = 1;
		$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
		exit();
	}

	$oauth_token = $rsp['oauth_token'];

	$foursquare_user = foursquare_users_get_by_oauth_token($oauth_token);

	if (($foursquare_user) && ($user_id = $foursquare_user['user_id'])){
		$user = users_get_by_id($user_id);
	}

	# Token swap stuff - use with care and paranoia
	# See comments in config.php.example for details.
	# (20140302/straup)

	else if (features_is_enabled("oauth_token_swap")){

		$args = array(
			'oauth_token' => $oauth_token,
		);

		$rsp = foursquare_api_call('users/self', $args);

		if (! $rsp['ok']){
			$GLOBALS['error']['foursquare_userinfo'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		$foursquare_id = $rsp['rsp']['user']['id'];
		$email = $rsp['rsp']['user']['contact']['email'];

		if (! $email){
			$GLOBALS['error']['token_swap'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		$user = users_get_by_email($email);

		if (! $user){
			$GLOBALS['error']['token_swap'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		$foursquare_user = foursquare_users_get_by_user_id($user['id']);

		if (! $foursquare_user){
			$GLOBALS['error']['token_swap'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		# No really, let's be strict about things.

		if ($foursquare_user['foursquare_id'] != $foursquare_id){
			$GLOBALS['error']['token_swap'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		$update = array('oauth_token' => $oauth_token);
		$rsp = foursquare_users_update_user($foursquare_user, $update);

		if (! $rsp['ok']){
			$GLOBALS['error']['token_swap'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		$foursquare_user = $rsp['foursquare_user'];
	}

	# If we don't ensure that new users are allowed to create
	# an account (locally).

	else if (! $GLOBALS['cfg']['enable_feature_signup']){
		$GLOBALS['smarty']->display("page_signup_disabled.txt");
		exit();
	}

	# Hello, new user! This part will create entries in two separate
	# databases: Users and FoursquareUsers that are joined by the primary
	# key on the Users table.

	else {

		$args = array(
			'oauth_token' => $oauth_token,
		);

		$rsp = foursquare_api_call('users/self', $args);

		if (! $rsp['ok']){
			$GLOBALS['error']['foursquare_userinfo'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		$foursquare_id = $rsp['rsp']['user']['id'];
		$username = $rsp['rsp']['user']['firstName'];
		$email = $rsp['rsp']['user']['contact']['email'];

		if (! $email){
			$email = "{$foursquare_id}@donotsend-foursquare.com";
		}

		if (isset($rsp['rsp']['user']['lastName'])){
			$username .= " {$rsp['rsp']['user']['lastName']}";
		}

		$password = random_string(32);

		$user = users_create_user(array(
			"username" => $username,
			"email" => $email,
			"password" => $password,
		));

		if (! $user){
			$GLOBALS['error']['dberr_user'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}

		$foursquare_user = foursquare_users_create_user(array(
			'user_id' => $user['id'],
			'oauth_token' => $oauth_token,
			'foursquare_id' => $foursquare_id,
		));

		if (! $foursquare_user){
			$GLOBALS['error']['dberr_foursquareuser'] = 1;
			$GLOBALS['smarty']->display("page_auth_callback_foursquare_oauth.txt");
			exit();
		}
	}

	# Okay, now finish logging the user in (setting cookies, etc.) and
	# redirecting them to some specific page if necessary.

	$redir = (isset($extra['redir'])) ? $extra['redir'] : '';

	login_do_login($user, $redir);
	exit();
?>
