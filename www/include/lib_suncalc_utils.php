<?php

 	#################################################################

	function suncalc_utils_timestampify($times){

		$ts_times = array();

		foreach ($times as $label => $date){
			$ts_times[$label] = strtotime($date);
		}

		asort($ts_times);
		return $ts_times;
	}

 	#################################################################

	function suncalc_utils_get_timeofday($ts, $times){

		$timeofday = null;

		foreach ($times as $label => $when){

			$timeofday = $label;

			if ($when > $ts){
				break;
			}
		}

		return $timeofday;
	}

 	#################################################################

?>
