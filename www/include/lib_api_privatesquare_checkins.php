<?php

	loadlib("privatesquare_checkins");

 	#################################################################

	function api_privatesquare_checkins_delete(){

		$checkin_id = post_int32("checkin_id");

		if (! $checkin_id){
			api_output_error(999, "Missing checkin ID");
		}

		$owner = $GLOBALS['cfg']['user'];

		$checkin = privatesquare_checkins_get_by_id($owner, $checkin_id);

		if (! $checkin){
			api_output_error(999, "Invalid checkin ID");
		}

		$rsp = privatesquare_checkins_delete($checkin);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		$out = array(
			'venue_id' => $checkin['venue_id'],
			'foursquare_checkin' => $checkin['checkin_id'],
		);

		api_output_ok($out);
	}

 	#################################################################
?>
