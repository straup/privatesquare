<?php

	loadlib("nypl_gazetteer");

	#################################################################

	function venues_nypl_fetch_venue($venue_id){

		$path = "/place/{$venue_id}.json";
		$rsp = nypl_gazetteer_get($path);

		return $rsp;
	}

	#################################################################

	# the end	
