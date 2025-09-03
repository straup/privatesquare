<?php

	include("include/init.php");

	loadlib("api");
	loadlib("api_methods");

	features_ensure_enabled(array(
		"api",
		"api_www",
		"api_documentation",
	));

	if ($GLOBALS['cfg']['api_require_loggedin']){
		login_ensure_loggedin();
	}

	$method_classes = array();
	$method_names = array();

	ksort($GLOBALS['cfg']['api']['methods']);

	$user_id = ($GLOBALS['cfg']['user']) ? $GLOBALS['cfg']['user']['id'] : 0;

	foreach ($GLOBALS['cfg']['api']['methods'] as $method_name => $details){

		$details['name'] = $method_name;

		if (! api_methods_can_view_method($details, $user_id)){
			continue;
		}

		$parts = explode(".", $method_name);
		array_pop($parts);

		$method_prefix = $parts[0];
		$method_class = implode(".", $parts);

		if ((! isset($method_classes[$method_class])) || (! is_array($method_classes[$method_class]))){

			$method_classes[$method_class] = array(
				'methods' => array(),
				'prefix' => $method_prefix,
			);
		}

		$method_classes[$method_class]['methods'][] = $details;
		$method_names[] = $details['name'];
	}

	foreach ($method_classes as $class_name => $ignore){
		usort($method_classes[$class_name]['methods'], function($a, $b) {
			return strcmp($a['name'], $b['name']);
		});
	}

	$GLOBALS['smarty']->assign("method_classes", $method_classes);

	$formats = $GLOBALS['cfg']['api']['formats'];
	$GLOBALS['smarty']->assign("response_formats", $formats);

	if (get_isset("print")){
		$GLOBALS['smarty']->display("page_api_methods_print.txt");
		exit();
	}

	$GLOBALS['smarty']->display("page_api_methods.txt");
	exit();
?>
