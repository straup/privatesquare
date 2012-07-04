<?php

	loadlib("privatesquare_checkins");

 	#################################################################

	function api_privatesquare_checkins_updateStatus(){

		$checkin = _api_privatesquare_checkins_get_checkin();

		if (! post_isset("status_id")){
			api_output_error(999, "Missing status ID");
		}

		$status_id = post_int32("status_id");

		if (! privatesquare_checkins_is_valid_status($status_id)){
			api_output_error(999, "Invalid status");
		}

		$update = array(
			'status_id' => $status_id,
		);


		$rsp = privatesquare_checkins_update($checkin, $update);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		$status_map = privatesquare_checkins_status_map();

		$out = array(
			'status_id' => $status_id,
			'label' => $status_map[$status_id],
		);

		api_output_ok($out);
	}

 	#################################################################

	function api_privatesquare_checkins_delete(){

		# TO DO: update to use _api_privatesquare_checkins_get_checkin
		# once it's proven to work... (20120703/straup)

		if (! $GLOBALS['cfg']['enable_feature_delete_checkins']){
			api_output_error(999, "Deleting checkins is currently disabled.");
		}

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

	function _api_privatesquare_checkins_get_checkin(){

		$checkin_id = post_int32("checkin_id");

		if (! $checkin_id){
			api_output_error(999, "Missing checkin ID");
		}

		$owner = $GLOBALS['cfg']['user'];

		$checkin = privatesquare_checkins_get_by_id($owner, $checkin_id);

		if (! $checkin){
			api_output_error(999, "Invalid checkin ID");
		}

		return $checkin;
	}

 	#################################################################
?>
