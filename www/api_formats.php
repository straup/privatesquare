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

	$default = $GLOBALS['cfg']['api']['default_format'];

	$formats = $GLOBALS['cfg']['api']['formats'];
	ksort($formats);

	$GLOBALS['smarty']->assign("default", $default);
	$GLOBALS['smarty']->assign("formats", $formats);

	$GLOBALS['smarty']->display("page_api_formats.txt");
	exit();

?>
