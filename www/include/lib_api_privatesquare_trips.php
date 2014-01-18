<?php

	loadlib("trips");

 	#################################################################

	function api_privatesquare_trips_addTrip(){

		$woeid = post_int32("woeid");
		$arrival = post_str("arrival");
		$departure = post_str("departure");

		if (! $woeid){
			api_output_error("Required parameter missing: woeid");
		}

		if (! $arrival){
			api_output_error(999, "Required parameter missing: arrival");
		}

		list($y, $m, $d) = explode("-", $arrival, 3);

		if (! checkdate($m, $d, $y)){
			api_output_error(999, "Invalid arrival date");
		}

		if (! $departure){
			api_output_error(999, "Required parameter missing: departure");
		}

		list($y, $m, $d) = explode("-", $departure, 3);

		if (! checkdate($m, $d, $y)){
			api_output_error(999, "Invalid departure date");
		}

		$diff = date_diff(date_create($arrival), date_create($departure));

		if (($diff->days) && ($diff->interval)){
			api_output_error(999, "How can you depart before you arrive? Are you a time lord?");
		}

		$arrive_by = post_int32("arrive_by");
		$depart_by = post_int32("depart_by");

		if (($arrive_by) && (! trips_is_valid_travel_type($arrive_by))){
			api_output_error(999, "Invalid arrival travel type");
		}

		if (($depart_by) && (! trips_is_valid_travel_type($depart_by))){
			api_output_error(999, "Invalid departure travel type");
		}

		$status_id = post_int32("status_id");

		if (! trips_is_valid_status_id($status_id)){
			api_output_error(999, "Invalid status ID");
		}

		$note = post_str("note");

		#

		$trip = array(
			'user_id' => $GLOBALS['cfg']['user']['id'],
			'locality_id' => $woeid,
			'arrival' => $arrival,
			'departure' => $departure,
			'arrive_by_id' => $arrive_by,
			'depart_by_id' => $depart_by,
			'status_id' => $status_id,
			'note' => $note,
		);

		$rsp = trips_add_trip($trip);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		$out = array(
			'trip' => $rsp['trip'],
		);

		api_output_ok($out);
	}

 	#################################################################

	function api_privatesquare_trips_editTrip(){

		$trip = _api_privatesquare_trips_get_trip();
		$update = array();

		#

		if (post_isset("woeid")){

			$woeid = post_int32("woeid");

			if (! $woeid){
				api_output_error(999, "Invalid WOE ID");
			}

			$rsp = whereonearth_fetch_woeid($woeid);

			if (! $rsp['ok']){
				api_output_error(999, "Failed to retrieve WOE ID");
			}

			$update['locality_id'] = $woeid;
		}

		if (post_isset("arrival")){

			$arrival = post_str("arrival");

			list($y, $m, $d) = explode("-", $arrival, 3);

			if (! checkdate($m, $d, $y)){
				api_output_error(999, "Invalid arrival date");
			}

			$update['arrival'] = $arrival;
		}

		if (post_isset("departure")){

			$departure = post_str("departure");

			list($y, $m, $d) = explode("-", $departure, 3);

			if (! checkdate($m, $d, $y)){
				api_output_error(999, "Invalid departure date");
			}

			$update['departure'] = $departure;
		}

		$arrival = ($update['arrival']) ? $update['arrival'] : $trip['arrival'];
		$departure = ($update['departure']) ? $update['departure'] : $trip['departure'];

		$diff = date_diff(date_create($arrival), date_create($departure));

		if (($diff->days) && ($diff->interval)){
			api_output_error(999, "How can you depart before you arrive? Are you a time lord?");
		}

		if (post_isset("arrive_by")){

			$arrive_by = post_int32("arrive_by");

			if (! trips_is_valid_travel_type($arrive_by)){
				api_output_error(999, "Invalid arrival travel type");
			}

			$update['arrive_by_id'] = $arrive_by;
		}

		if (post_isset("depart_by")){

			$depart_by = post_int32("depart_by");

			if (! trips_is_valid_travel_type($depart_by)){
				api_output_error(999, "Invalid departure travel type");
			}

			$update['depart_by_id'] = $depart_by;
		}

		if (post_isset("status_id")){

			$status_id = post_int32("status_id");

			# validate here...
			$update['status_id'] = $status_id;
		}

		if (! trips_is_valid_status_id($status_id)){
			api_output_error(999, "Invalid status ID");
		}

		if (post_isset("note")){
			$update['note'] = post_str("note");
		}

		#

		foreach ($update as $k => $v){

			if ($update[$k] == $trip[$k]){
				unset($update[$k]);
			}
		}

		if (! count($update)){
			api_output_error(999, "Hey! There's nothing to update");
		}

		#

		$rsp = trips_update_trip($trip, $update);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		$out = array(
			'trip' => $rsp['trip'],
		);

		api_output_ok($out);
	}

 	#################################################################

	function api_privatesquare_trips_deleteTrip(){

		$trip = _api_privatesquare_trips_get_trip();

		$rsp = trips_delete_trip($trip);

		if (! $rsp['ok']){
			api_output_error(999, $rsp['error']);
		}

		api_output_ok();
	}

 	#################################################################

	function _api_privatesquare_trips_get_trip(){

		# TO DO: dopplr IDs (20140118/straup)

		$trip_id = post_int64("id");

		if (! $trip_id){
			api_output_error(999, "Missing trip ID");
		}

		$trip = trips_get_by_id($trip_id);

		if (! $trip){
			api_output_error(999, "Invalid trip ID");
		}

		if ($trip['user_id'] != $GLOBALS['cfg']['user']['id']){
			api_output_error(999, "Insufficient permissions");
		}

		return $trip;
	}

 	#################################################################

	# the end
