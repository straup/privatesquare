<?php

	#################################################################

	function venues_stateofmind_venues(){

		$venues = array(
			array('id' => '51783653', 'name' => 'in a car'),
			array('id' => '51783655', 'name' => 'in a cab'),
			array('id' => '52204087', 'name' => 'on a bike'),
			array('id' => '68247267', 'name' => 'on a bus'),
			array('id' => '51783659', 'name' => 'on a train'),
			array('id' => '51783657', 'name' => 'on a plane'),
			array('id' => '68247339', 'name' => 'ON A ZEPPELIN!!!'),
			array('id' => '51783661', 'name' => 'on a boat'),
			array('id' => '52204085', 'name' => 'on the water'),
			array('id' => '68246787', 'name' => 'on a highway'),
			array('id' => '68246789', 'name' => 'at an intersection'),
			array('id' => '51866911', 'name' => 'in transit'),
			array('id' => '68234601', 'name' => 'in a meeting'),
			array('id' => '68234603', 'name' => 'on a telco'),
			array('id' => '68247671', 'name' => 'watching sportsball'),
			array('id' => '68247553', 'name' => 'hotel coffee'),
			array('id' => '52204033', 'name' => 'wish you were here'),
			array('id' => '52204035', 'name' => 'kill me now'),
			array('id' => '52204045', 'name' => 'EXTERMINATE!!!!'),
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
