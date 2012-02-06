<?php

	##############################################################################

	function datetime_when_is_valid_date($str){
		$dt = _datetime_when_prep_datetime($str, 0);
		return ($dt) ? 1 : 0;
	}

	##############################################################################

	function datetime_when_parse($str){

		list($start, $stop) = explode(";", $str, 2);

		if (! $stop){
			$stop = $start;
		}

		$dates = array();

		$dates[] = _datetime_when_prep_datetime($start, 0);
		$dates[] = _datetime_when_prep_datetime($stop, 1);

		return $dates;
	}

	##############################################################################

	function _datetime_when_prep_datetime($dt, $tail=0){

		preg_match("/^(\d{4})(?:-(\d{2})(?:-(\d{2})(?:[\sT](\d{2})(?:\:(\d{2})(?:\:(\d{2})(?:\s?(-?\d+|[A-Z]{3}))?)?)?)?)?)?/", $dt, $m);

		$count = count($m);

		if ($count == 2){
			$dt = ($tail) ? "{$m[1]}-12-31T23:59:59" : "{$m[1]}-01-01T00:00:00";
		}

		# TO DO: months with less that 31 days...

		else if ($count == 3){
			$dt = ($tail) ? "{$m[1]}-{$m[2]}-31T23:59:59" : "{$m[1]}-{$m[2]}-01T00:00:00";
		}

		else if ($count == 4){
			$dt = ($tail) ? "{$m[1]}-{$m[2]}-${m[3]}T23:59:59" : "{$m[1]}-{$m[2]}-{$m[3]}T00:00:00";
		}

		else if ($count == 5){
			$dt = ($tail) ? "{$m[1]}-{$m[2]}-{$m[3]}T{$m[4]}:59:59" : "{$m[1]}-{$m[2]}-{$m[3]}T{$m[4]}:00:00";
		}

		else if ($count == 6){
			$dt = ($tail) ? "{$m[1]}-{$m[2]}-{$m[3]}T{$m[4]}:{$m[5]}:59" : "{$m[1]}-{$m[2]}-{$m[3]}T{$m[4]}:{$m[5]}:00";
		}

		else if ($count == 7){
			$dt = "{$m[1]}-{$m[2]}-{$m[3]}T{$m[4]}:{$m[5]}:{$m[6]}";
		}

		else if ($count == 8){
			$dt = "{$m[1]}-{$m[2]}-{$m[3]}T{$m[4]}:{$m[5]}:{$m[6]} {$m[7]}";
		}

		else {
			return null;
		}

		$ts = strtotime($dt);
		$dt = gmdate('Y-m-d H:i:s', $ts);
		
		return $dt;
	}

	##############################################################################
?>
