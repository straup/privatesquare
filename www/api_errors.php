<?php

	include("include/init.php");

	features_ensure_enabled(array(
		"api",
		"api_www",
		"api_documentation",
	));

	if ($GLOBALS['cfg']['api_require_loggedin']){
		login_ensure_loggedin();
	}

	$errors = $GLOBALS['cfg']['api']['errors'];
	ksort($errors);

	$GLOBALS['smarty']->assign("errors", $errors);

	$GLOBALS['smarty']->display("page_api_errors.txt");
	exit();

?>
