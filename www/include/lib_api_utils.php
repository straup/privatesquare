<?php

	##############################################################################

	function api_utils_ensure_pagination_args(&$args, $method_row=null){

		$per_page_default = $GLOBALS['cfg']['api_per_page_default'];
		$per_page_max = $GLOBALS['cfg']['api_per_page_max'];

		if ($method_row){
			if (isset($method_row["pagination_per_page_max"])){
				$per_page_max = $method_row["pagination_per_page_max"];
			}
		}

		if ($page = request_int32("page")){
			$args['page'] = $page;
		}

		if ($per_page = request_int32("per_page")){
			$args['per_page'] = $per_page;
		}

		if (! $args['page']){
			$args['page'] = 1;
		}

		if (! $args['per_page']){
			$args['per_page'] = $per_page_default;
		}

		else if ($args['per_page'] > $per_page_max){
			$args['per_page'] = $per_page_max;
		}

		if ($cursor = request_str("cursor")){
			$args['cursor'] = $cursor;
		}

		# note the pass by ref
	}

	##############################################################################

	function api_utils_ensure_pagination_results(&$out, &$pagination){

		$out['next_query'] = null;
		 
		$method = request_str("method");
		$method_row = $GLOBALS['cfg']['api']['methods'][$method];

		$query = array(
			"method" => $method,
		);

		if (is_array($method_row["parameters"])){

			foreach ($method_row["parameters"] as $p){

				$k = $p["name"];

				if ($v = request_str($k)){
					$query[$k] = $v;
				}
			}
		}

		if (($format = request_str("format")) && (in_array($format, $GLOBALS['cfg']['api']['formats']))){
			$query["format"] = $format;
		}

		if ((features_is_enabled("api_extras")) && ($method_row["extras"])){

			if ($e = request_str("extras")){
				$query["extras"] = $e;
			}
		}

		if (isset($pagination['cursor'])){

			# on the one hand it would be good and nice to include these for consistency's
			# sake (with say null values) but I have a feeling their presence will just be
			# confusing... we'll see, I guess (20170222/thisisaaronland)

			if ($pagination['total_count']){

				$out['total'] = $pagination['total_count'];
				$out['page'] = null;
				$out['pages'] = $pagination['page_count'];
			}

			$out['per_page'] = $pagination['per_page'];
			$out['cursor'] = $pagination['cursor'];

			if ($cursor = $out['cursor']){

				$query['per_page'] = $out['per_page'];
				$query["cursor"] = $cursor;

				$next_query = http_build_query($query);
				$out['next_query'] = $next_query;
			}
		}

		else {

			$out['total'] = $pagination['total_count'];
			$out['page'] = $pagination['page'];
			$out['per_page'] = $pagination['per_page'];
			$out['pages'] = $pagination['page_count'];
			$out['cursor'] = null;

			if (($out['page'] + 1) < $out['pages']){

				$query['per_page'] = $out['per_page'];
				$query['page'] = $out['page'] + 1;

				$next_query = http_build_query($query);
				$out['next_query'] = $next_query;
			}
		}

		# note the pass by ref
	}
	
	##############################################################################

	function api_utils_features_ensure_enabled($f){

		if (! features_is_enabled($f)){
			api_output_error(503);
		}
	}

	##############################################################################

	# https://secure.php.net/manual/en/features.file-upload.php
	# https://secure.php.net/manual/en/features.file-upload.post-method.php

	function api_utils_ensure_upload($param, $more=array()){

		$rsp = api_utils_get_upload($param, $more);

		if (! $rsp['ok']){

			$code = $rsp["code"] || 450;
			api_output_error($code, $rsp['error']);
		}

		return $rsp;
	}

	########################################################################

	function api_utils_get_upload($param, $more=array()){

		$defaults = array(
			'ensure_mimetype' => array()
		);

		$more = array_merge($defaults, $more);

		if (! isset($_FILES[$param])){
			return array('ok' => 0, 'error' => "Missing upload parameter", 'code' => 453);
		}

		if (! is_array($_FILES[$param])){
			return array('ok' => 0, 'error' => "Invalid upload parameter", "code" => 454);
		}

		$upload = $_FILES[$param];

		if (! isset($upload['error'])){
			return array('ok' => 0, 'error' => "Missing upload error response", "code" => 455);
		}

		if (is_array($upload['error'])){
			return array('ok' => 0, 'error' => "Invalid upload error response", "code" => 455);
		}

		switch ($upload['error']) {
			case UPLOAD_ERR_OK:
				break;
			case UPLOAD_ERR_NO_FILE:
				return array('ok' => 0, 'error' => "Missing body", "code" => 456);
			case UPLOAD_ERR_INI_SIZE:
				# pass
			case UPLOAD_ERR_FORM_SIZE:
				return array('ok' => 0, 'error' => "Exceeded filesize", "code" => 457);
			default:
				return array('ok' => 0, 'error' => "INVISIBLE ERROR CAT", "code" => 450);
		}

		if (! is_uploaded_file($upload['tmp_name'])){
			return array('ok' => 0, 'error' => "Invalid upload file name", "code" => 454);
		}

		if ($upload['size'] == 0){
			return array('ok' => 0, 'error' => "Invalid file size", "code" => 454);
		}

		$finfo = finfo_open(FILEINFO_MIME_TYPE);
		$mime = finfo_file($finfo, $upload['tmp_name']);

		if (count($more['ensure_mimetype'])){

			if (! in_array($mime, $more['ensure_mimetype'])){
				return array('ok' => 0, 'error' => "Invalid mime type", "code" => 458);
			}
		}

		return array('ok' => 1, 'upload' => $upload, 'mimetype' => $mime);
	}

	########################################################################

	# the end
