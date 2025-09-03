<?php

	include("include/init.php");

	loadlib("api");
	loadlib("api_spec");
	loadlib("api_methods");

	features_ensure_enabled(array(
		"api",
		"api_www",
		"api_documentation",
	));

	if ($GLOBALS['cfg']['api_require_loggedin']){
		login_ensure_loggedin();
	}

	$method = get_str("method");

	if (! $method){
		error_404();
	}

	if (! isset($GLOBALS['cfg']['api']['methods'][$method])){
		error_404();
	}

	$details = $GLOBALS['cfg']['api']['methods'][$method];

	if (( $GLOBALS['cfg']['user']) && (! api_methods_can_view_method($details, $GLOBALS['cfg']['user']['id']))){
		error_404();
	}

	$rsp = api_spec_utils_example_for_method($method);

	if ($rsp['ok']){
		$details['example_response'] = $rsp['example'];
	}

	# TO DO: convert markdown in $details

	$rsp_formats = array();

	foreach ($GLOBALS['cfg']['api']['formats'] as $fmt => $fmt_details){

		if (($fmt_details["enabled"]) && ($fmt_details["documented"])){
			$rsp_formats[]= $fmt;
		}
	}

	$GLOBALS['smarty']->assign("method", $method);
	$GLOBALS['smarty']->assign("response_formats", $rsp_formats);
	$GLOBALS['smarty']->assign("default_format", $GLOBALS['cfg']['api']['default_format']);

	$GLOBALS['smarty']->assign("details", $details);

	$GLOBALS['smarty']->display("page_api_method.txt");
	exit();
?>
