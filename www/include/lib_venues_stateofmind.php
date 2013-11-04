<?php

	#################################################################

	function venues_stateofmind_venues(){

		$venues = array(
			array('venue_id' => '51783653', 'name' => 'in a car'),
			array('venue_id' => '51783655', 'name' => 'in a cab'),
			array('venue_id' => '51783657', 'name' => 'on a plane'),
			array('venue_id' => '51783659', 'name' => 'on a train'),
			array('venue_id' => '51783661', 'name' => 'on a boat'),
		);

		return $venues;
	}

	#################################################################

	function venues_stateofmind_fetch_venue($venue_id){

		$venues = venues_stateofmind_venues();

		foreach ($venues as $v){

			if ($v['venue_id'] == $venue_id){
				return $v;
			}
		}
	}

	#################################################################

	# the end
