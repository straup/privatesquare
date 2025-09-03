<?php

	include("include/init.php");
	loadlib("api");

	features_ensure_enabled(array(
		"api",
		"api_www",
		"api_documentation"
	));
	
	if ($GLOBALS['cfg']['api_require_loggedin']){
		login_ensure_loggedin();
	}

	$GLOBALS['smarty']->display("page_api.txt");
	exit();
?>
