<?php

	include("include/init.php");
	loadlib("foursquare_users");

	$whoami = $_SERVER['REQUEST_URI'];
	login_ensure_loggedin($whoami);

	$fsq_user = foursquare_users_get_by_user_id($GLOBALS['cfg']['user']['id']);

	if (! $fsq_user){
		error_404();
	}

	$path = urlencode(get_str("path"));

	$url = "{$GLOBALS['cfg']['abs_root_url']}user/{$fsq_user['foursquare_id']}/{$path}";

	header("location: {$url}");
	exit();
?>
