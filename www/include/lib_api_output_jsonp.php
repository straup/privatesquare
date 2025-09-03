<?php

	loadlib("http_codes");

	#################################################################

	function api_output_ok($rsp=array(), $more=array()){

		$callback = api_output_jsonp_ensure_valid_callback();
		api_output_send($rsp, $callback, $more);
	}

	#################################################################

	function api_output_error($code=999, $msg='', $more=array()){

		$more['is_error'] = 1;

		$out = array('error' => array(
			'code' => $code,
			'error' => $msg,	# deprecated - https://github.com/cooperhewitt/parallel-tms/issues/272
			'message' => $msg,
		));

		api_log($out);

		api_output_send($out, $more);
	}

	#################################################################

	function api_output_send($rsp, $callback, $more=array()){

		$rsp['stat'] = (isset($more['is_error'])) ? 'error' : 'ok';

		api_log(array('stat' => $rsp['stat']), 'write');

		api_output_utils_start_headers($rsp, $more);

		if (features_is_enabled("api_cors")){
			if ($origin = $GLOBALS['cfg']['api_cors_allow_origin']){
				header("Access-Control-Allow-Origin: " . htmlspecialchars($origin));
			}
		}

		$json = json_encode($rsp);

		# http://miki.it/blog/2014/7/8/abusing-jsonp-with-rosetta-flash/

		$jsonp = "/**/" . $callback . "(" . $json . ")";

		header("Content-Disposition: attachment; filename=f.txt,");
		header("X-Content-Type-Options: nosniff");
		header("Content-Length: " . strlen($jsonp));

		if (! request_isset("inline")){
			header("Content-Type: application/javascript");
		}
		
		echo $jsonp;
		exit();
	}

	function api_output_jsonp_ensure_valid_callback(){
		# see http://stackoverflow.com/questions/3128062/is-this-safe-for-providing-jsonp

		$callback = get_str("callback");
		
		if(api_output_jsonp_ensure_valid_function_name($callback) && api_output_jsonp_ensure_no_reserved_words($callback)) {
			return $callback;
		} else {
			api_output_jsonp_send_fatal();
		}
	}

	function api_output_jsonp_send_fatal($rsp, $more=array()) {
		http_codes_send_headers(400);

		header("X-API-Error-Code: 999");
		header("X-API-Error-Message: Invalid callback");
		exit();
	}

	function api_output_jsonp_ensure_valid_function_name($callback) {
		//this regex makes sure the callback name is a valid function name per the ECMAScript spec. it also allows periods for namespaced callbacks.
		//as far as XSS is concerned, it means callback "names" containing parens, curly braces, semicolons and slashes will result in a 400
		//see: http://www.geekality.net/2011/08/03/valid-javascript-identifier/

		$identifier_syntax = '/^[$_\p{L}\.][$_\p{L}\p{Mn}\p{Mc}\p{Nd}\p{Pc}\x{200C}\x{200D}\.]*+$/u';
		return preg_match($identifier_syntax, $callback);
	}

	function api_output_jsonp_ensure_no_reserved_words($callback) {
		$reserved_words = array('break', 'do', 'instanceof', 'typeof', 'case',
			'else', 'new', 'var', 'catch', 'finally', 'return', 'void', 'continue', 
			'for', 'switch', 'while', 'debugger', 'function', 'this', 'with', 
			'default', 'if', 'throw', 'delete', 'in', 'try', 'class', 'enum', 
			'extends', 'super', 'const', 'export', 'import', 'implements', 'let', 
			'private', 'public', 'yield', 'interface', 'package', 'protected', 
			'static', 'null', 'true', 'false');
		return (in_array(mb_strtolower($callback, 'UTF-8'), $reserved_words)) ? 0 : 1;
	}

	#################################################################

	# the end
