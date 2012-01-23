<?php

	include("include/init.php");
	loadlib("foursquare_api");

	$redir = $GLOBALS['cfg']['abs_root_url'] . get_str('redir');

	# Some basic sanity checking like are you already logged in?

	if ($GLOBALS['cfg']['user']['id']){
		header("location: {$redir}");
		exit();
	}

	if (! $GLOBALS['cfg']['enable_feature_signin']){
		$GLOBALS['smarty']->display("page_signin_disabled.txt");
		exit();
	}

	# TO DO: pass redir around...

	$url = foursquare_api_get_auth_url();

	header("location: {$url}");
	exit();
?>
