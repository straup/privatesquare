<?php

	loadlib("suncalc");
	loadlib("suncalc_api");
	loadlib("suncalc_utils");

 	#################################################################

	function suncalc_simple($ts, $lat, $lon){

		$date = date('c', $ts);

		if ($GLOBALS['cfg']['enable_feature_suncalc_api']){

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
		}

		else {
			# these are totally unreliable right now
			# (20120306/straup)

			return not_okay("why are you not using the api?");

			$times = suncalc_get_times($date, $lat, $lon);
			$pos = suncalc_get_position($date, $lat, $lon);
		}

		$tod = suncalc_utils_get_timeofday($ts, $times);

		return okay(array(
			'position' => $pos,
			'timeofday' => $tod
		));
	}

 	#################################################################
?>
