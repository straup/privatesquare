<?php

	# See also: http://php.net/manual/en/function.date.php

	loadlib("privatesquare_checkins_timezones");
	loadlib("reverse_geoplanet");

	#################################################################

	function privatesquare_checkins_dates_format_ymd(&$checkin){
		return privatesquare_checkins_dates_format($checkin, "Y-m-d");
	}

	#################################################################

	function privatesquare_checkins_dates_format_date(&$checkin){
		return privatesquare_checkins_dates_format($checkin, "M d, Y");
	}

	#################################################################

	function privatesquare_checkins_dates_format_time(&$checkin){
		return privatesquare_checkins_dates_format($checkin, "H:i");
	}

	#################################################################

	function privatesquare_checkins_dates_format_where(&$checkin){

		$locality = $checkin['locality'];

		if (! $locality){
			return null;
		}

		if (! is_array($locality)){
			$locality = reverse_geoplanet_get_by_woeid($locality, 'locality');
		}

		if (! $locality){
			return null;
		}

		$parts = explode(", ", $locality['name']);
		return $parts[0];
	}

	#################################################################

	function privatesquare_checkins_dates_format_timezone(&$checkin){
		return privatesquare_checkins_dates_format($checkin, "T");
	}

	#################################################################

	function privatesquare_checkins_dates_format(&$checkin, $fmt){

		if (! $checkin['timezone']){
			return null;
		}

		$tzid = timezones_woeid_to_tzid($checkin['timezone']);

		if (! $tzid){
			return null;
		}

		try {
			$ts = $checkin['created'];
			$dt = new DateTime("@$ts");

			$tz = new DateTimeZone($tzid);
			$dt->setTimezone($tz);

			return $dt->format($fmt);
		}

		catch (Exception $e){
			log_error("[PRIVATESQUARE] failed to format date for checkin, because: " . $e->getMessage());
			return null;
		}

	}

	#################################################################

	# the end
