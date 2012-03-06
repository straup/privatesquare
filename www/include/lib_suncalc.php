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


	/* 
	var m   = Math,
	    rad = m.PI / 180,
	    sin = m.sin,
	    cos = m.cos;
	*/

	// constants for sun calculations

	define(SUNCALC_RAD, M_PI / 180);

	define(SUNCALC_DAYSMS, (1000 * 60 * 60 * 24));
	define(SUNCALC_J1970, 2440588);
	define(SUNCALC_J2000, 2451545);
	define(SUNCALC_M0, SUNCALC_RAD * 357.5291);
	define(SUNCALC_M1, SUNCALC_RAD * 0.98560028);
	define(SUNCALC_J0, 0.0009);
	define(SUNCALC_J1, 0.0053);
	define(SUNCALC_J2, -0.0069);
	define(SUNCALC_C1, SUNCALC_RAD * 1.9148);
	define(SUNCALC_C2, SUNCALC_RAD * 0.0200);
	define(SUNCALC_C3, SUNCALC_RAD * 0.0003);
	define(SUNCALC_P, SUNCALC_RAD * 102.9372);
	define(SUNCALC_e, SUNCALC_RAD * 23.45);
	define(SUNCALC_th0, SUNCALC_RAD * 280.1600);
	define(SUNCALC_th1, SUNCALC_RAD * 360.9856235);

	// date conversions

	function suncalc_dateToJulianDate(date){
		# FIX ME
		return date.valueOf() / SUNCALC_DAYSMS - 0.5 + SUNCALC_J1970;
	}

	function suncalc_julianDateToDate(j){
		# FIX ME
		return new Date((j + 0.5 - SUNCALC_J1970) * SUNCALC_DAYSMS);
	}

	// general sun calculations

	function getJulianCycle(J, lw) { return m.round(J - J2000 - J0 - lw / (2 * m.PI)); }
	function getSolarMeanAnomaly(Js) { return M0 + M1 * (Js - J2000); }
	function getEquationOfCenter(M) { return C1 * sin(M) + C2 * sin(2 * M) + C3 * sin(3 * M); }
	function getEclipticLongitude(M, C) { return M + P + C + m.PI; }
	function getSunDeclination(Ls) { return m.asin(sin(Ls) * sin(e)); }


	// calculations for sun times

	function getApproxTransit(Ht, lw, n) { return J2000 + J0 + (Ht + lw) / (2 * m.PI) + n; }
	function getSolarTransit(Js, M, Ls) { return Js + (J1 * sin(M)) + (J2 * sin(2 * Ls)); }
	function getHourAngle(h, phi, d) { return m.acos((sin(h) - sin(phi) * sin(d)) / (cos(phi) * cos(d))); }


	// calculations for sun position

	function getRightAscension(Ls) { return m.atan2(sin(Ls) * cos(e), cos(Ls)); }
	function getSiderealTime(J, lw) { return th0 + th1 * (J - J2000) - lw; }
	function getAzimuth(H, phi, d) { return m.atan2(sin(H), cos(H) * sin(phi) - m.tan(d) * cos(phi)); }
	function getAltitude(H, phi, d) { return m.asin(sin(phi) * sin(d) + cos(phi) * cos(d) * cos(H)); }


	function suncalc_add_time($angle, $rise_name, $set_name){
		$GLOBALS['suncalc_times'][] = array($angle, $rise_name, $set_name);
	}

	# calculates sun times for a given date and latitude/longitude

	function suncalc_get_times($date, $lat, $lon){

		var lw    = SUNCALC_RAD * -lng,
		    phi   = SUNCALC_RAD * lat,
		    J     = dateToJulianDate(date),
		    n     = getJulianCycle(J, lw),
		    Js    = getApproxTransit(0, lw, n),
		    M     = getSolarMeanAnomaly(Js),
		    C     = getEquationOfCenter(M),
		    Ls    = getEclipticLongitude(M, C),
		    d     = getSunDeclination(Ls),
		    Jnoon = getSolarTransit(Js, M, Ls);

		function getSetJ(h) {
			var w = getHourAngle(h, phi, d),
			    a = getApproxTransit(w, lw, n);

			return getSolarTransit(a, M, Ls);
		}

		var result = {solarNoon: julianDateToDate(Jnoon)};

		var i, len, time, angle, morningName, eveningName, Jset, Jrise;
		for (i = 0, len = times.length; i < len; i += 1) {
			time = times[i];

			angle       = time[0];
			morningName = time[1];
			eveningName = time[2];

			Jset  = getSetJ(angle * SUNCALC_RAD);
			Jrise = Jnoon - (Jset - Jnoon);

			result[morningName] = julianDateToDate(Jrise);
			result[eveningName] = julianDateToDate(Jset);
		}

		return result;
	};


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

?>
