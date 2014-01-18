<?php

	########################################################################

	function trips_dopplr_travel_types_map(){

	}

	########################################################################

	function trips_dopplr_import_trip(&$dopplr_trip, &$user){

		$trip = array(
			'user_id' => $user['id'],
			'dopplr_id' => $dopplr_trip['id'],
			# 'dopplr_data' => json_encode($dopplr_trip),
			'arrival' => $dopplr_trip['start'],
			'departure' => $dopplr_trip['finish'],
			'locality_id' => $dopplr_trip['city']['woeid'],
			'status_id' => 1,
		);

		# TO DO: sort out transport type

		dumper($trip);

		$rsp = trips_add_trip($trip);
		# dumper($rsp);

	}

	########################################################################

	# end
