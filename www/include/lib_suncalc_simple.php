<?php

	loadlib("suncalc_api");
	loadlib("suncalc_utils");

 	#################################################################

	function suncalc_simple($ts, $lat, $lon){

		# TO DO: finish php libs; check API stuff here...

		$date = date('c', $ts);

		$args = array(
			'date' => $date,
			'lat' => 37.756532,
			'lon' => -122.422149
		);

		$reqs = array(
			array('times', $args),
			array('position', $args)
		);

		$rsp = suncalc_api_call_multi($reqs);

		if (! $rsp['ok']){
			return $rsp;
		}

		$times = $rsp['rows'][0]['times'];
		$pos = $rsp['rows'][1]['position'];

		$tod = suncalc_utils_get_timeofday($ts, $times);

		return okay(array(
			'position' => $pos,
			'timeofday' => $tod
		));
	}

 	#################################################################
?>
