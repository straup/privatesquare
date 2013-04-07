<?php

	include("include/init.php");

	login_ensure_loggedin();
	features_ensure_enabled("youarehere");

	loadlib("youarehere_users");
	loadlib("youarehere_api");

	$auth_url = youarehere_api_auth_user_url();
	$GLOBALS['smarty']->assign("auth_url", $auth_url);

	$GLOBALS['smarty']->display("page_youarehere.txt");
	exit();

?>
