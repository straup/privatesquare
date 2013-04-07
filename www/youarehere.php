<?php

	include("include/init.php");

	login_ensure_loggedin();
	features_ensure_enabled("youarehere");

	loadlib("youarehere_users");
	loadlib("youarehere_api");

	if ($youarehere_user = youarehere_users_get_by_user_id($GLOBALS['cfg']['user']['id'])){
		$GLOBALS['smarty']->assign_by_ref("youarehere_user", $youarehere_user);
	}

	$auth_url = youarehere_api_authenticate_user_url();
	$GLOBALS['smarty']->assign("auth_url", $auth_url);

	$GLOBALS['smarty']->display("page_youarehere.txt");
	exit();

?>
