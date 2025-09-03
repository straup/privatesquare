<?php

 	#################################################################

	# HEY LOOK! RUNNING CODE!!!

	$start = microtime_ms();

	loadlib("api_config");
	api_config_init();

	$end = microtime_ms();
	$time = $end - $start;

	$GLOBALS['timing_keys']["api_init"] = "API init";
	$GLOBALS['timings']['api_init_count'] = 1;
	$GLOBALS['timings']['api_init_time'] = $time;

 	#################################################################

	loadlib("api_output");
	loadlib("api_log");

	loadlib("api_auth");
	loadlib("api_errors");
	loadlib("api_formats");
	loadlib("api_keys");
	loadlib("api_keys_utils");
	loadlib("api_throttle");
	loadlib("api_utils");

	#################################################################

	function api_dispatch($method){

		if (! $GLOBALS['cfg']['enable_feature_api']){
			api_output_error(503, 'API disabled');
		}

		# Necessary? Also causes PHP 5.5 to freak out
		# with older versions of lib_filter...
		# (20140122/straup)

		$method = filter_strict($method);

		$api_key = request_str("api_key");
		$access_token = request_str("access_token");

		if ($access_token == ""){
			$access_token = "-";
		}
		
		# Log the basics

		$addr = remote_addr();

		$params = api_log_request_params();

		api_log(array(
			'method' => $method,
			'params' => $params,
			'access_token_hash' => hash("sha256", $access_token),
			'remote_addr' => $addr,
		));

		# CORS pre-flight stuff
		# https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS

		if (($_SERVER['REQUEST_METHOD'] == "OPTIONS") && ($GLOBALS['cfg']['enable_feature_api_cors'])){

			header("HTTP/1.1 204 No Content");
			api_cors_headers();
			exit();
		}	

		# This no longer works in PHP8 (php-fpm)
		# apache_setenv("API_METHOD", $method);

		# I do not like this but it will do for now
		$GLOBALS["cfg"]["api_current_method"] = $method;
		
		$methods = $GLOBALS['cfg']['api']['methods'];

		if ((! $method) || (! isset($methods[$method]))){
			$enc_method = htmlspecialchars($method);
			api_output_error(499);
		}


		$method_row = $methods[$method];

		$key_row = null;
		$token_row = null;

		if (! $method_row['enabled']){
			$enc_method = htmlspecialchars($method);
			api_output_error(499);
		}

		$method_row['name'] = $method;

		if ($GLOBALS['cfg']['api_auth_type'] == 'oauth2'){

			if (($_SERVER['REQUEST_METHOD'] != 'POST') && (! $GLOBALS['cfg']['api_oauth2_allow_get_parameters'])){
			 	api_output_error(405);
			}
		}

		if (isset($method_row['request_method'])){

			$allowed = $method_row['request_method'];

			if (! is_array($allowed)){
				$allowed = array($allowed);
			}

			if (! in_array($_SERVER['REQUEST_METHOD'], $allowed)){
				api_output_error(405);
			}
		}

		# Make sure this is a valid output format for the method in question

		if (($format = api_output_get_format()) && (isset($method_row["disallow_formats"])) && (is_array($method_row["disallow_formats"]))){

			if (in_array($format, $method_row["disallow_formats"])){ 
				api_output_error(497);
			}
		}

		# Okay – now we get in to validation and authorization. Which means a
		# whole world of pedantic stupid if we're using Oauth2. Note that you
		# could use OAuth2 and require API keys be passed explictly but since
		# that's not part of the spec if you enable the two features simultaneously
		# don't be surprised when hilarity ensues. Good times. (20121026/straup)

		# First API keys
 
		if (features_is_enabled("api_require_keys")){

			if (! $api_key){
				api_output_error(484);
			}

			$key_row = api_keys_get_by_key($api_key);
			api_keys_utils_ensure_valid_key($key_row);

			api_log(array(
				'api_key_id' => $key_row['id'],
				'api_key_user_id' => $key_row['user_id'],
				'api_key_role_id' => $key_row['role_id'],
			));
		}

		# Second auth-y bits

		$auth_rsp = api_auth_ensure_auth($method_row, $key_row);

		if (isset($auth_rsp['api_key'])){
			$key_row = $auth_rsp['api_key'];
			api_log(array(
				'api_key_id' => $key_row['id'],
				'api_key_user_id' => $key_row['user_id'],				
			));
		}

		if (isset($auth_rsp['access_token'])){
			$token_row = $auth_rsp['access_token'];
			api_log(array(
				'access_token_id' => $token_row['id'],
				'access_token_user_id' => $token_row['user_id'],				
			));
		}
	
		if (isset($auth_rsp['user'])){
			$GLOBALS['cfg']['user'] = $auth_rsp['user'];
			# api_log(array('user_id' => $auth_rsp['user']['id']));
		}

		# This no longer works in PHP8 (php-fpm)
		# apache_setenv("API_KEY", $key_row['api_key']);

		# Check for require-iness of users here ?

		# Roles - for API keys (things like only the site keys)

		api_config_ensure_roles($method_row, $key_row, $token_row);

		# Blessings and other method specific access controls

		api_config_ensure_blessing($method_row, $key_row, $token_row);

		# Finally, crumbs - because they are tastey

		if ((isset($method_row['requires_crumb'])) && ($method_row['requires_crumb'])){
			api_auth_ensure_crumb($method_row);
		}

		# GO!

		loadlib($method_row['library']);

		$parts = explode(".", $method);
		$method = array_pop($parts);

		$func = "{$method_row['library']}_{$method}";

		if (! function_exists($func)){
			api_output_error(499);
		}

		# see the way we're passing $method_row to the function?
		# not much uses that yet... (20171101/thisisaaronland)

		call_user_func_array($func, array($method_row));
		exit();
	}

	#################################################################
	
	function api_cors_headers(){

		if (count($GLOBALS['cfg']['api_cors_allow_origin'])){
			$origins = implode(",", $GLOBALS['cfg']['api_cors_allow_origin']);
			header("Access-Control-Allow-Origin: " . htmlspecialchars($origins));
		}

		if (count($GLOBALS['cfg']['api_cors_allow_methods'])){
			$methods = implode(",", $GLOBALS['cfg']['api_cors_allow_methods']);
			header("Access-Control-Allow-Methods: " . htmlspecialchars($methods));
		}
			
		if (count($GLOBALS['cfg']['api_cors_allow_headers'])){
		   	$headers = implode(",", $GLOBALS['cfg']['api_cors_allow_headers']);
			header("Access-Control-Allow-Headers: " . htmlspecialchars($headers));
		}

	}
	
	#################################################################

	# the end
