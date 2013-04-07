<?php

	include("include/init.php");

	loadlib("youarehere_users");
	loadlib("youarehere_api");

	login_ensure_loggedin();

	features_ensure_enabled("youarehere");

	if (youarehere_users_get_by_user_id($GLOBALS['cfg']['user']['id'])){
		$url = $GLOBALS['cfg']['abs_root_url'] . "youarehere/";
		header("location: {$url}");
		exit();
	}

	$code = get_str("code");

	if (! $code){
		$url = $GLOBALS['cfg']['abs_root_url'] . "youarehere/";
		header("location: {$url}");
		exit();
	}

	$rsp = youarehere_api_get_access_token($code);

	if (! $rsp['ok']){

	}

	else if ($rsp['data']['error']){

	}

	else if (! $rsp['data']['access_token']){

	}

	else if ($rsp['data']['scope'] != 'write'){

	}

	else {

		$data = array(
			'user_id' => $GLOBALS['cfg']['user']['id'],
			'access_token' => $rsp['data']['access_token'],
		);

		# $rsp = youarehere_users_add_user($data);
	}

	exit();
?>
