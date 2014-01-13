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

		$loc = geo_flickr_get_woeid($woeid);

		if (! $loc){
			api_output_error(999, "Failed to retrieve city");
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

		$note = post_str("note");

		#

		$trip = array(
			'user_id' => $GLOBALS['cfg']['user']['id'],
			'locality' => $woeid,
			'arrival' => $arrival,
			'departure' => $departure,
			'arrival_type_id' => $arrive_by,
			'departure_type_id' => $depart_by,
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

	# the end
