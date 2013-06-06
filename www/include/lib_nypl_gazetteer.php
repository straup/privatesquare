<?php

	loadlib("http");

	$GLOBALS['nypl_gazetteer_endpoint'] = 'http://dev.nypl.gazetteer.in/1.0/';

	########################################################################

	function nypl_gazetteer_get($path, $args, $more=array()){

		$path = ltrim($path, "/");
		$query = http_build_query($args);

		$url = $GLOBALS['nypl_gazetteer_endpoint'] . $path . "?" . $query;
		$rsp = http_get($url);

		return nypl_gazetteer_parse_response($rsp);
	}

	########################################################################

	function nypl_gazetteer_parse_response($rsp){

		$data = json_decode($rsp['body'], "as hash");

		if (! $data){
			$rsp['ok'] = 0;
			$rsp['error'] = "JSON parse error";
			return $rsp;
		}

		$rsp['data'] = $data;
		return $rsp;
	}

	########################################################################

	# the end
