<?php

	#################################################################

	function venues_stateofmind_venues(){

		$venues = array(
			array('id' => '51783653', 'name' => 'in a car'),
			array('id' => '51783655', 'name' => 'in a cab'),
			array('id' => '51783657', 'name' => 'on a plane'),
			array('id' => '51783659', 'name' => 'on a train'),
			array('id' => '51783661', 'name' => 'on a boat'),
			array('id' => '51866911', 'name' => 'in transit'),
			array('id' => '51866909', 'name' => 'so confused'),
		);

		return $venues;
	}

	#################################################################

	function venues_stateofmind_fetch_venue($venue_id){

		$venues = venues_stateofmind_venues();

		foreach ($venues as $v){

			if ($v['id'] == $venue_id){
				return $v;
			}
		}
	}

	#################################################################

	# the end
