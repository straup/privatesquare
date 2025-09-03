<?php

	$GLOBALS['api_log'] = array();

	########################################################################

	function api_log($data, $dispatch=0){

		$GLOBALS['api_log'] = array_merge($GLOBALS['api_log'], $data);

		if (! $dispatch){
			return array('ok' => 1);
		}

		$provider = api_log_provider();	
		$func = "api_log_{$provider}_dispatch";

		$remote_addr = remote_addr();
		$remote_addr_int = ip2long($remote_addr);

		$id = dbtickets_create(64);

		$more = array(
		      	"id" => $id,
			"hostname" => gethostname(),
			"remote_addr" => $remote_addr_int,
			"created" => time(),
		);

		$msg = array_merge($GLOBALS['api_log'], $more);

		$as_int	= array(
			"api_key_id",
                        "api_key_user_id",
                        "api_key_role_id",
			"access_token_id",
			"access_token_user_id", 		
                );

		foreach	($as_int as $k){
		        if (isset($msg[$k])){
	                        $msg[$k] = intval($msg[$k]);
                        }
		}

		# Don't just blindly write any old query string in to the database
		# At the very least check that it's something we care about (and
		# hope that upstream code is checking/validating those values...)
		
		if ((isset($msg["params"])) && (count($msg["params"]))){

			$params = $msg["params"];
			$filtered_params = array();

			$m = $GLOBALS['cfg']['api']['methods'][$msg["method"]];

			if ((isset($m["parameters"])) && (is_array($m["parameters"]))){

				$allowed_params = $m["parameters"];

				if ((isset($m["paginated"])) && ($m["paginated"])){
					// This is not ideal but it will have to do for now...
					$allowed_params[] = array("name" => "page");
					$allowed_params[] = array("name" => "per_page");
					$allowed_params[] = array("name" => "cursor");										
				}

				foreach ($allowed_params as $p){

					$n = $p["name"];
					
					if (isset($params[$n])){
						$filtered_params[$n] = $params[$n];
					}
				}
			}

			$msg["params"] = $filtered_params;
		}

		$msg["stat"] = ($msg["stat"] == "ok") ? 1 : 0;
		
		$rsp = call_user_func($func, $msg);

		if (! $rsp["ok"]){
			error_log("Failed to dispatch API log to {$func}, {$rsp["error"]}");
		}
		
		$GLOBALS['api_log'] = array();
		return $rsp;
	}

	########################################################################

	# A simple helper function to gather and scrub API parameters for
	# passing along to the logging facilities (20140616/straup)

	function api_log_request_params(){

		$params = $_REQUEST;
		unset($params['access_token']);
		unset($params['method']);

		return $params;
	}

	########################################################################

	function api_log_provider(){

		$provider = $GLOBALS['cfg']['api_log_use_module'];

		if (! preg_match("/^[a-z_]+$/", $provider)){
			die("Missing or invalid login provider.");
		}

		$provider_lib = "api_log_{$provider}";
		loadlib($provider_lib);

		return $provider;
	}

	# the end
