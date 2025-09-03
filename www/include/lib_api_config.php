<?php

	#################################################################

	function api_config_init(){

		$GLOBALS['cfg']['api_server_scheme'] = ($GLOBALS['cfg']['api_require_ssl']) ? 'https' : 'http';
		$GLOBALS['cfg']['api_server_name'] = parse_url($GLOBALS['cfg']['abs_root_url'], PHP_URL_HOST);
		$GLOBALS['cfg']['api_server_port'] = parse_url($GLOBALS['cfg']['abs_root_url'], PHP_URL_PORT);

		if ($GLOBALS['cfg']['api_server_port']){

			# these are chunked in to separate statements just because they are
			# long and hard to read as a single test (20160212/thisisaaronland)

	                if (($GLOBALS['cfg']['api_server_scheme'] == 'http') && ($GLOBALS['cfg']['api_server_port'] != 80)){

				$GLOBALS['cfg']['api_server_name'] .= ':' . $GLOBALS['cfg']['api_server_port'];

			} else if (($GLOBALS['cfg']['api_server_scheme'] == 'https') && ($GLOBALS['cfg']['api_server_port'] != 443)){

				$GLOBALS['cfg']['api_server_name'] .= ':' . $GLOBALS['cfg']['api_server_port'];

			} else {}
		}

		if ($GLOBALS['cfg']['abs_root_suffix']){
			$GLOBALS['cfg']['api_abs_root_url'] = $GLOBALS['cfg']['api_abs_root_url'] . $GLOBALS['cfg']['abs_root_suffix'] . "/";
		}

		# If I have an API specific subdomain/prefix then check to see if I am already
		# running on that host; if not then update the 'api_server_name' config

		if (($GLOBALS['cfg']['api_subdomain']) && (! preg_match("/^{$GLOBALS['cfg']['api_subdomain']}/", $GLOBALS['cfg']['api_server_name']))){
			$GLOBALS['cfg']['api_server_name'] = $GLOBALS['cfg']['api_subdomain'] . $GLOBALS['cfg']['api_server_name'];
		}

		# Build the 'api_abs_root_url' based on everything above

		if ($GLOBALS['cfg']['api_abs_root_url'] == ""){
			$GLOBALS['cfg']['api_abs_root_url'] = "{$GLOBALS['cfg']['api_server_scheme']}://{$GLOBALS['cfg']['api_server_name']}" . "/";
		}

		# If I have an API specific subdomain/prefix then check to see if I am already
		# running on that host; if I am then update the 'site_abs_root_url' config and
		# use it in your code accordingly.

		if (($GLOBALS['cfg']['api_subdomain']) && (preg_match("/^{$GLOBALS['cfg']['api_subdomain']}/", $GLOBALS['cfg']['api_server_name']))){

			# the old way (20150513/copea)
			# $GLOBALS['cfg']['site_abs_root_url'] = str_replace("{$GLOBALS['cfg']['api_subdomain']}", "", $GLOBALS['cfg']['abs_root_url']);

			$server_name = $GLOBALS['cfg']['api_server_name'];
			$server_name = preg_replace("/^{$GLOBALS['cfg']['api_subdomain']}/", "", $server_name);

			$GLOBALS['cfg']['site_abs_root_url' ] = "{$GLOBALS['cfg']['api_server_scheme']}://{$server_name}/";

			# see this – we need to do this because we call $GLOBALS['cfg']['abs_root_url'] all
			# over lib_whatever land (20150513/copea)

			$GLOBALS['cfg']['abs_root_url'] = $GLOBALS['cfg']['site_abs_root_url'];
		}

		else {
			$GLOBALS['cfg']['site_abs_root_url'] = $GLOBALS['cfg']['abs_root_url'];
		}

		# Load methods / blessings

		# $GLOBALS['timing_keys']["api_config_methods"] = "API methods";
		# $GLOBALS['timings']['api_config_methods_count'] = 0;
		# $GLOBALS['timings']['api_config_methods_time'] = 0;

		$start = microtime_ms();

		foreach ($GLOBALS['cfg']['api_method_definitions'] as $def){

			try {
				$path = FLAMEWORK_INCLUDE_DIR . "/config_api_methods_{$def}.php";
				include_once($path);

				# $GLOBALS['timings']['api_config_methods_count'] += 1;
			}

			catch (Exception $e){
				# $msg = $e->getMessage();
				api_config_freakout_and_die();
			}
		}

		$end = microtime_ms();
		$time = $end - $start;

		# $GLOBALS['timings']['api_config_methods_time'] = $time;

		foreach ($GLOBALS['cfg']['api_errors_definitions'] as $def){

			try {
				$path = FLAMEWORK_INCLUDE_DIR . "/config_api_errors_{$def}.php";
				include_once($path);

				# $GLOBALS['timings']['api_config_methods_count'] += 1;
			}

			catch (Exception $e){
				# $msg = $e->getMessage();
				api_config_freakout_and_die();
			}
		}

		if ($GLOBALS['cfg']['enable_feature_api_method_overrides']){
			api_config_init_overrides();
		}

		if ($GLOBALS['cfg']['enable_feature_api_method_aliases']){
			api_config_init_aliases();
		}

		api_config_init_blessings();
	}

	#################################################################

	function api_config_init_overrides(){

		foreach ($GLOBALS['cfg']['api']['method_overrides']['method_classes'] as $class_spec => $override){

			foreach ($GLOBALS['cfg']['api']['methods'] as $method_name => $method_details){

				if (! preg_match("/^{$class_spec}/", $method_name)){
					continue;
				}

				$GLOBALS['cfg']['api']['methods'][$method_name] = array_merge($GLOBALS['cfg']['api']['methods'][$method_name], $override);
			}
		}

		foreach ($GLOBALS['cfg']['api']['method_overrides']['methods'] as $method_name => $override){

			if (! isset($GLOBALS['cfg']['api']['methods'][$method_name])){
				continue;
			}

			$GLOBALS['cfg']['api']['methods'][$method_name] = array_merge($GLOBALS['cfg']['api']['methods'][$method_name], $override);
		}

	}

	#################################################################

	function api_config_init_aliases(){

		foreach ($GLOBALS['cfg']['api']['method_aliases']['method_classes'] as $class_spec => $aliases){

			foreach ($GLOBALS['cfg']['api']['methods'] as $method_name => $method_details){

				if (! preg_match("/^{$class_spec}/", $method_name)){
					continue;
				}

				foreach ($aliases as $alias_spec => $alias_details){

					$method_alias = str_replace($class_spec, $alias_spec, $method_name);

					# apply any overrides that may be necessary
					$alias_details = array_merge($method_details, $alias_details);

					$GLOBALS['cfg']['api']['methods'][$method_alias] = $alias_details;
				}
			}
		}

		foreach ($GLOBALS['cfg']['api']['method_aliases']['methods'] as $method_name => $aliases){

			if (! isset($GLOBALS['cfg']['api']['methods'][$method_name])){
				continue;
			}

			foreach ($aliases as $method_alias => $alias_details){

				# apply any overrides that may be necessary
				$alias_details = array_merge($method_details, $alias_details);

				$GLOBALS['cfg']['api']['methods'][$method_alias] = $alias_details;
			}
		}

	}

	#################################################################

	function api_config_init_blessings(){

		# $GLOBALS['timing_keys']["api_blessings"] = "API blessings";
		# $GLOBALS['timings']['api_blessings_count'] = 0;
		# $GLOBALS['timings']['api_blessings_time'] = 0;

		foreach ($GLOBALS['cfg']['api']['blessings'] as $api_key => $key_details){

			# $GLOBALS['timings']['api_blessings_count'] += 1;

			$start = microtime_ms();
			$whoami = $api_key;

			if ($api_key == 'site_key'){

				loadlib("api_keys");

				$blessed_site_keys = (features_is_enabled(array("api_site_keys", "api_site_keys_blessed"))) ? 1 : 0;

				if (($blessed_site_keys) && ($site_key = api_keys_fetch_site_key())){

					$api_key = $site_key['api_key'];
				}
			}

			$blessing_defaults = array();

			foreach (array('hosts', 'tokens', 'environments') as $prop){
				if (isset($key_details[$prop])){
					$blessing_defaults[$prop] = $key_details[$prop];
				}
			}

			if (is_array($key_details['method_classes'])){

				foreach ($key_details['method_classes'] as $class_spec => $blessing_details){

					foreach ($GLOBALS['cfg']['api']['methods'] as $method_name => $method_details){

						if (! isset($method_details['requires_blessing']) || (! $method_details['requires_blessing'])){
							continue;
						}

						if (! preg_match("/^{$class_spec}/", $method_name)){
							continue;
						}

						$blessing = array_merge($blessing_defaults, $blessing_details);
						_api_config_apply_blessing($method_name, $api_key, $blessing);
					}
				}
			}

			if (is_array($key_details['methods'])){

				foreach ($key_details['methods'] as $method_name => $blessing_details){

					$blessing = array_merge($blessing_defaults, $blessing_details);
					_api_config_apply_blessing($method_name, $api_key, $blessing);
				}
			}

			# _api_config_apply_blessing('api.test.isBlessed', $api_key, $blessing_defaults);

			$end = microtime_ms();
			$time = $end - $start;

			# $GLOBALS['timings']['api_blessings_time'] += $time;
		}
	}

 	#################################################################

	function _api_config_apply_blessing($method_name, $api_key, $blessing=array()){

		if (! isset($GLOBALS['cfg']['api']['methods'][$method_name])){
		   	error_log("Attempting to apply blessings to method name ({$method_name}) that does not exist.");
			return;
		}
		
		if (! is_array($GLOBALS['cfg']['api']['methods'][$method_name]['blessings'])){
			$GLOBALS['cfg']['api']['methods'][$method_name]['blessings'] = array();
		}

		$GLOBALS['cfg']['api']['methods'][$method_name]['blessings'][$api_key] = $blessing;
	}

	#################################################################

	function api_config_ensure_blessing($method_row, $key_row, $token_row=null){

		if ((isset($method_row['requires_blessing'])) && ($method_row['requires_blessing'])){

			$blessings = $method_row['blessings'];
			$api_key = $key_row['api_key'];

			if (! isset($blessings[$api_key])){
				api_output_error(403, "This key has not been configured for use with this API method");
			}

			$details = $blessings[$api_key];

			if (isset($details['environments'])){

				if (! in_array($GLOBALS['cfg']['environment'], $details['environments'])){
					api_output_error(403, 'Invalid host environment');
				}
			}

			if (isset($details['hosts'])){

				$addr = remote_addr();

				if (! in_array($addr, $details['hosts'])){
					api_output_error(403, "Invalid host: '{$addr}'");
				}
			}

			if (isset($details['tokens'])){

				if (! $token_row){
					api_output_error(403, 'Invalid token');
				}

				if (! in_array($token_row['access_tokens'], $details['tokens'])){
					api_output_error(403, 'Invalid token');
				}
			}

			if (isset($details['user_roles'])){

				$user = $GLOBALS['cfg']['user'];

				if (! $user){
					api_output_error(403, 'Insufficient permissions');
				}

				if ((! is_array($details['user_roles'])) || (! count($details['user_roles']))){
					api_output_error(403, 'Permissions are incorrectly configured, defaulting to NO.');
				}

				if (! auth_has_role_all($details['user_roles'], $user['id'])){
					api_output_error(403, 'Insufficient permissions');
				}
			}

			else if (isset($details['user_roles_any'])){

				$user = $GLOBALS['cfg']['user'];

				if (! $user){
					api_output_error(403, 'Insufficient permissions');
				}

				if ((! is_array($details['user_roles_any'])) || (! count($details['user_roles_any']))){
					api_output_error(403, 'Permissions are incorrectly configured, defaulting to NO.');
				}

				if (! auth_has_role_any($details['user_roles_any'], $user['id'])){
					api_output_error(403, 'Insufficient permissions');
				}
			}

			else {}

			# Ensure that site keys with blessings have been configured to require
			# a set of roles (20140814/straup)

			if (api_keys_is_site_key($key_row)){

				if ((! isset($details['user_roles'])) && (! isset($details['user_roles_any']))){
					api_output_error(403, 'Permissions are incorrectly configured, defaulting to NO.');
				}
			}
		}

		return 1;
	}

	#################################################################

	# For example:
	#
	# "foo.bar.addBaz" => array(
	# 	"description" => "",
	# 	"documented" => 0,
	#	"enabled" => 1,
	#	"requires_blessing" => 0,
	#	"requires_key_role" => array("site"),
	#	"requires_user_role" => array("staff"),
	#
	# As in: require a site key for someone who is staff
	# (20140307/straup)

	function api_config_ensure_roles(&$method, &$key, &$token){

		$roles_map = api_keys_roles_map();

		if ((isset($method['requires_key_role'])) && (is_array($method['requires_key_role']))){

			$role_id = $key['role_id'];
			$role = $roles_map[$role_id];

			if (! in_array($role, $method['requires_key_role'])){
				api_output_error(403, "Insufficient permissions for API key");
			}
		}

		elseif (isset($method['requires_key_role'])){
			api_output_error(403, "Insufficient permissions for API key (because the server is misconfigured)");
		}

		else {}

		if ((isset($method['requires_user_role'])) && (is_array($method['requires_user_role']))){

			if (! auth_has_role_any($method['requires_user_role'], $token['user_id'])){
				api_output_error(403, "Insufficient permissions for API key");
			}
		}

		else if (isset($method['requires_user_role'])){
			api_output_error(403, "Insufficient permissions for API key (because the server is misconfigured)");
		}

		else {}

		return 1;
	}

	#################################################################

	function api_config_freakout_and_die($code=500, $reason=null){

		$msg = "The API is currently throwing a temper tantrum. That's not good.";

		if ($reason){
			$msg .= " This is what we know so far: {$reason}.";
		}

		# Because if we're here it's probably because the actual config
		# file is busted (20121026/straup)

		if (! isset($GLOBALS['cfg']['api']['default_format'])){
			$GLOBALS['cfg']['api']['default_format'] = 'json';
		}

		loadlib("api_output");
		loadlib("api_log");

		api_output_error($code, $msg);
		exit();
	}

	#################################################################

	# the end
