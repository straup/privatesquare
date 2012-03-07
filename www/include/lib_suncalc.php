<?php

	# This is a port of Vladimir Agafonkin's SunCalc,
	# a JavaScript library for calculating sun position
	# and sunlight phases. See also:
	# https://github.com/mourner/suncalc

	$GLOBALS['suncalc_times'] = array(
		array(-0.83, 'sunrise', 'sunset'),
		array(-0.3, 'sunriseEnd', 'sunsetStart'),
		array(-6, 'dawn', 'dusk'),
	        array(-12, 'nauticalDawn', 'nauticalDusk'),
		array(-18, 'nightEnd', 'night'),
		array(6, 'goldenHourEnd', 'goldenHour')
	);

	# constants for sun calculations

	define('SUNCALC_RAD', M_PI / 180);
	define('SUNCALC_DAYSMS', (1000 * 60 * 60 * 24));
	define('SUNCALC_J1970', 2440588);
	define('SUNCALC_J2000', 2451545);
	define('SUNCALC_M0', SUNCALC_RAD * 357.5291);
	define('SUNCALC_M1', SUNCALC_RAD * 0.98560028);
	define('SUNCALC_J0', 0.0009);
	define('SUNCALC_J1', 0.0053);
	define('SUNCALC_J2', -0.0069);
	define('SUNCALC_C1', SUNCALC_RAD * 1.9148);
	define('SUNCALC_C2', SUNCALC_RAD * 0.0200);
	define('SUNCALC_C3', SUNCALC_RAD * 0.0003);
	define('SUNCALC_P', SUNCALC_RAD * 102.9372);
	define('SUNCALC_e', SUNCALC_RAD * 23.45);
	define('SUNCALC_th0', SUNCALC_RAD * 280.1600);
	define('SUNCALC_th1', SUNCALC_RAD * 360.9856235);

	# date conversions

 	#################################################################

	function suncalc_date_to_julian_date($date){
		$ms = strtotime($date) * 1000;
		return $ms / SUNCALC_DAYSMS - 0.5 + SUNCALC_J1970;
	}

 	#################################################################

	function suncalc_julian_date_to_date($j){
		$ms = ($j + 0.5 - SUNCALC_J1970) * SUNCALC_DAYSMS;
		$ts = $ms / 1000;
		return gmdate('c', $ts);
	}

	# general sun calculations

 	#################################################################

	function suncalc_get_julian_cycle($J, $lw){
		return round($J - SUNCALC_J2000 - SUNCALC_J0 - $lw / (2 * M_PI));
	}

 	#################################################################

	function suncalc_get_solar_mean_anomaly($Js){
		return SUNCALC_M0 + SUNCALC_M1 * ($Js - SUNCALC_J2000);
	}

 	#################################################################

	function suncalc_get_equation_of_center($M){
		return SUNCALC_C1 * sin($M) + SUNCALC_C2 * sin(2 * $M) + SUNCALC_C3 * sin(3 * $M);
	}

 	#################################################################

	function suncalc_get_ecliptic_longitude($M, $C){
		return $M + SUNCALC_P + $C + M_PI;
	}

 	#################################################################

	function suncalc_get_sun_declination($Ls){
		return asin(sin($Ls) * sin(SUNCALC_e));
	}

 	#################################################################

	# calculations for sun times

 	#################################################################

	function suncalc_get_approx_transit($Ht, $lw, $n){
		return SUNCALC_J2000 + SUNCALC_J0 + ($Ht + $lw) / (2 * M_PI) + $n;
	}

 	#################################################################

	function suncalc_get_solar_transit($Js, $M, $Ls){
		return $Js + (SUNCALC_J1 * sin($M)) + (SUNCALC_J2 * sin(2 * $Ls));
	}

 	#################################################################

	function suncalc_get_hour_angle($h, $phi, $d){
		return acos((sin($h) - sin($phi) * sin($d)) / (cos($phi) * cos($d)));
	}

 	#################################################################

	# calculations for sun position

 	#################################################################

	function suncalc_get_right_ascension($Ls){
		return atan2(sin($Ls) * cos(SUNCALC_e), cos($Ls));
	}

 	#################################################################

	function suncalc_get_sidereal_time($J, $lw){
		return SUNCALC_th0 + SUNCALC_th1 * ($J - SUNCALC_J2000) - $lw;
	}

 	#################################################################

	function suncalc_get_azimuth($H, $phi, $d) {
		return atan2(sin($H), cos($H) * sin($phi) - tan($d) * cos($phi));
	}

 	#################################################################

	function suncalc_get_altitude($H, $phi, $d){
		return asin(sin($phi) * sin($d) + cos($phi) * cos($d) * cos($H));
	}

 	#################################################################

	function suncalc_add_time($angle, $rise_name, $set_name){
		$GLOBALS['suncalc_times'][] = array($angle, $rise_name, $set_name);
	}

 	#################################################################

	# calculates sun times for a given date and latitude/longitude

 	#################################################################

	function suncalc_get_times($date, $lat, $lon){

		$lw = SUNCALC_RAD * -$lon;
		$phi = SUNCALC_RAD * $lat;

		$J = suncalc_date_to_julian_date($date);
		$n = suncalc_get_julian_cycle($J, $lw);

		$Js = suncalc_get_approx_transit(0, $lw, $n);
		$M = suncalc_get_solar_mean_anomaly($Js);
		$C = suncalc_get_equation_of_center($M);

		$Ls = suncalc_get_ecliptic_longitude($M, $C);
		$d = suncalc_get_sun_declination($Ls);

		$Jnoon = suncalc_get_solar_transit($Js, $M, $Ls);

		$result = array(
			'solarNoon' => suncalc_julian_date_to_date($Jnoon)
		);

		# var i, len, time, angle, morningName, eveningName, Jset, Jrise;

		foreach ($GLOBALS['suncalc_times'] as $time){

			# $Jset  = getSetJ(angle * SUNCALC_RAD);

			$angle       = $time[0];
			$morningName = $time[1];
			$eveningName = $time[2];

			$_h = $angle * SUNCALC_RAD;
			$_w = suncalc_get_hour_angle($_h, $phi, $d);
			$_a = suncalc_get_approx_transit($_w, $lw, $n);

			$JSet = suncalc_get_solar_transit($_a, $M, $ls);
			$Jrise = $Jnoon - ($Jset - $Jnoon);

			$result[$morningName] = suncalc_julian_date_to_date($Jrise);
			$result[$eveningName] = suncalc_julian_date_to_date($Jset);
		}

		return $result;
	}

 	#################################################################

	# calculates sun azimuth and altitude for a given date and latitude/longitude

	function suncalc_get_position($date, $lat, $lon){

		$lw  = SUNCALC_RAD * -$lon;
		$phi = SUNCALC_RAD * $lat;

		$J = suncalc_date_to_julian_date($date);
		$M = suncalc_get_solar_mean_anomaly($J);
		$C = suncalc_get_equation_of_center($M);
		$Ls = suncalc_get_ecliptic_longitude($M, $C);

		$d  = suncalc_get_sun_declination($Ls);

		$a = suncalc_get_right_ascension($Ls);
		$th = suncalc_get_sidereal_time($J, $lw);
		$H = $th - $a;

		return array(
			'azimuth' =>  suncalc_get_azimuth(H, phi, d),
			'altitude' => suncalc_get_altitude(H, phi, d)
		);
	}

 	#################################################################
?>
