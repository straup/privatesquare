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

	$fmt = get_str("format");

	if (! $fmt){
		error_404();
	}

	if (! isset($formats[$fmt])){
		error_404();
	}

	$format = $formats[$fmt];

	if (! $format['enabled']){
		error_404();
	}

	if (! $format['documented']){
		error_404();
	}

	$GLOBALS['smarty']->assign("default", $default);
	$GLOBALS['smarty']->assign("format", $fmt);

	$GLOBALS['smarty']->display("page_api_format.txt");
	exit();

?>
