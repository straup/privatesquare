<?php

	loadlib("brooklyn_integers_api");

	#################################################################

	function artisanal_integers_create(){

		if (! features_is_enabled("artisanal_integers")){
			return 0;
		}

		$rsp = brooklyn_integers_api_post("brooklyn.integers.create");

		if (! $rsp['ok']){
			return 0;
		}

		return $rsp['response']['integer'];
	}

	#################################################################

?>
