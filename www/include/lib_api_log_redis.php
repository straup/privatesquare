<?php

	loadlib("redis");

	########################################################################

	function api_log_redis_dispatch($data){

		return redis_publish($GLOBALS['cfg']['api_log_redis_channel'] , json_encode($data, JSON_FORCE_OBJECT));
	}

	########################################################################

	# the end