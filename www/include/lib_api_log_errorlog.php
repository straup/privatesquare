<?php

	########################################################################

	function api_log_errorlog_dispatch($data){

		$data = json_encode($data);
		error_log("[API] {$data}");

		return array('ok' => 1);
	}

	########################################################################

	# the end