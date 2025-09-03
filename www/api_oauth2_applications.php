<?php

	include("include/init.php");
	loadlib("api_keys");

	features_ensure_enabled(array(
		"api",
		"api_www",
		"api_delegated_auth",
	));

	login_ensure_loggedin();

	$more = array();

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = api_keys_for_user($GLOBALS['cfg']['user'], $more);
	$keys = $rsp['rows'];

	$GLOBALS['smarty']->assign("keys", $keys);

	$GLOBALS['smarty']->display("page_api_oauth2_applications.txt");
	exit();

?>
