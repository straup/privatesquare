<?php

	########################################################################

	function api_log_mysql_dispatch($data){
		
		$to_encode = array(
			"params",
                        "error",
		);

		foreach	($to_encode as $k){

                        if (! isset($data[$k])){
                        	continue;
			}

                        $data[$k] = json_encode($data[$k]);
                }

		return db_insert_api("ApiLogs", $data);
	}

	########################################################################

	# the end