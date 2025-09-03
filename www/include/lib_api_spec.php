<?php

	loadlib("api_spec_utils");

	# See also:
	# http://blog.linode.com/2012/04/04/api_spec/

 	#################################################################

	function api_spec_formats(){

		$formats = array();

		foreach ($GLOBALS['cfg']['api']['formats'] as $fmt => $details){

			if (! $details['enabled']){
				continue;
			}

			if (! $details['documented']){
				continue;
			}

			$formats[] = $fmt;
		}

		api_output_ok(array(
			'formats' => $formats,
			'default_format' => $GLOBALS['cfg']['api']['default_format']
		));
	}

 	#################################################################

	function api_spec_methods(){

		$export_keys = array(
			'request_method',
			'description',
			'requires_perms',
			'parameters',
			'errors',
			'notes',
			'example',
			'extras',
			'paginated',
			'pagination',
			'disallow_formats',
		);

		$export_keys_params = array(
			'name',
			'type',
			'description',
			'required',
			'example',
		);

		$defaults = array(
			'request_method' => 'GET',
			'requires_perms' => 0,
			'description' => '',
			'parameters' => array(),
			'errors' => new stdClass(),
			'extras' => false,
			'paginated' => false,
			'pagination' => 'plain',
			'disallow_formats' => array(),
		);

		$methods = array();

		foreach ($GLOBALS['cfg']['api']['methods'] as $name =>$details){

			if (! $details['enabled']){
				continue;
			}

			if (! $details['documented']){
				continue;
			}

			$details = array_merge($defaults, $details);

			$method = array(
				'name' => $name,
			);

			foreach ($export_keys as $k){

				if (! isset($details[$k])){
					continue;
				}

				$v = $details[$k];

				if ($k == "parameters"){

					$params_list = array();

					foreach ($v as $param_raw){

						if (! $param_raw["documented"]){
							continue;
						}

						$param = array();

						foreach ($export_keys_params as $p){

							if (isset($param_raw[$p])){
								$param[$p] = $param_raw[$p];							
							}
						}

						$params_list[] = $param;
					}

					$v = $params_list;
					
				} elseif ($k == "errors"){

					$errors_list = array();

					foreach ($v as $code => $details){

						if (! $details["documented"]){
							continue;
						}
						
						$errors_list[] = array(
							"code" => $code,
							"message" => $details["message"],
						);
					}

					$v = $errors_list;
				}
				
				$method[$k] = $v;
			}

			if (! $method['paginated']){
				unset($method['pagination']);
			}

			$methods[] = $method;
		}

		api_output_ok(array(
			'methods' => $methods
		));

	}

 	#################################################################

	function api_spec_errors(){

		$all_errors = $GLOBALS['cfg']['api']['errors'];
		ksort($all_errors);

		$rsp_errors = [];

		foreach ($all_errors as $code => $details){

			if (! $details['documented']){
				continue;
			}

			$details['code'] = $code;

			$rsp_errors[] = array(
				'code' => $code,
				'message' => $details['message']
			);
		}

		$out = array('errors' => $rsp_errors);
		api_output_ok($out);
	}

 	#################################################################

	# the end
