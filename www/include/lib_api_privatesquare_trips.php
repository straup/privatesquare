<?php

	loadlib("trips");

 	#################################################################

	function api_privatesquare_trips_add(){

		$woeid = post_int32("woeid");
		$arrival = post_str("arrival");
		$departure = post_str("departure");

		if (! $woeid){
			api_output_error("Required parameter missing: woeid");
		}

		if (! $arrival){
			api_output_error("Required parameter missing: arrival");
		}

		if (! $departure){
			api_output_error("Required parameter missing: departure");
		}

		# TO DO: validation

		$arrive_by = post_int32("arrive_by");
		$depart_by = post_int32("depart_by");

		if (($arrive_by) && (! trips_is_valid_travel_type($arrive_by))){
			api_output_error("Invalid arrival travel type");
		}

		if (($depart_by) && (! trips_is_valid_travel_type($depart_by))){
			api_output_error("Invalid departure travel type");
		}

		$note = post_str("note");

		$trip = array(
			'user_id' => $GLOBALS['cfg']['user']['id'],
			'woeid' => $woeid,
			'arrival' => $arrival,
			'departure' => $departure,
			'note' => $note,
			'arrival_type_id' => $arrive_by,
			'departure_type_id' => $depart_by,
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

	# the end
