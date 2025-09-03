<?php

	########################################################################

	$GLOBALS['cfg']['api']['methods'] = array_merge(array(

		"api.spec.methods" => array (
			"description" => "Return the list of available API response methods.",
			"documented" => features_is_enabled("api_documentation"),
			"enabled" => features_is_enabled("api_documentation"),
			"library" => "api_spec",
		),

		"api.spec.errors" => array (
			"description" => "Return the list of API error responses common to all methods.",
			"documented" => features_is_enabled("api_documentation"),
			"enabled" => features_is_enabled("api_documentation"),
			"library" => "api_spec",
		),

		"api.spec.formats" => array(
			"description" => "Return the list of valid API response formats, including the default format",
			"documented" => features_is_enabled("api_documentation"),
			"enabled" => features_is_enabled("api_documentation"),
			"library" => "api_spec",
		),

		"api.test.echo" => array(
			"description" => "A testing method which echo's all parameters back in the response.",
			"documented" => true,
			"enabled" => true,
			"library" => "api_test",
		),

		"api.test.error" => array(
			"description" => "Return a test error from the API",
			"documented" => true,
			"enabled" => true,
			"library" => "api_test",
		),

	), $GLOBALS['cfg']['api']['methods']);

	########################################################################

	# the end
