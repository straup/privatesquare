<?php

	########################################################################

	# map Dopplr travel types to privatesquare travel type IDs
	# (20140118/straup)

	function trips_dopplr_travel_types_map(){

		$map = array(
			'train' => 4,		# train
			'plane' => 6,		# plane
			'car' => 2,		# car
			'bus' => 3,		# bus
			'motorcycle' => 11,	# motorcycle
			'cycle'	=> 1,		# bicycle
			'walk' => 10,		# on foot	
			'other'	=> 0,		# none of your business
			'ferry'	=> 5,		# boat
		);

		return $map;
	}

	########################################################################

	function trips_dopplr_import_trip(&$dopplr_trip, &$user){

		$map = trips_dopplr_travel_types_map();

		$trip = array(
			'user_id' => $user['id'],
			'dopplr_id' => $dopplr_trip['id'],
			'arrival' => $dopplr_trip['start'],
			'departure' => $dopplr_trip['finish'],
			'arrive_by_id' => $map[$dopplr_trip['outgoing_transport_type']],
			'depart_by_id' => $map[$dopplr_trip['return_transport_type']],
			'locality_id' => $dopplr_trip['city']['woeid'],
			'status_id' => 1,
		);

		$rsp = trips_add_trip($trip);
		return $rsp;
	}

	########################################################################

	# end
