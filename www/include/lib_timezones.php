<?php

	#################################################################

	function timezones_get_by_tzid($tzid){

		$enc_id = AddSlashes($tzid);

		$sql = "SELECT * FROM Timezones WHERE tzid='{$enc_id}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	#################################################################

	function timezones_get_by_woeid($woeid){

		$enc_id = AddSlashes($woeid);

		$sql = "SELECT * FROM Timezones WHERE woeid='{$enc_id}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	#################################################################

	function timezones_tzid_to_woeid($tzid){

		$row = timezones_get_by_tzid($tzid);
		return ($row) ? $row['woeid'] : null;
	}

	#################################################################

	function timezones_woeid_to_tzid($woeid){

		$row = timezones_get_by_woeid($woeid);
		return ($row) ? $row['tzid'] : null;
	}

	#################################################################

	function timezones_get_for_latlon($lat, $lon, $point_in_poly=0){

		$enc_lat = AddSlashes($lat);
		$enc_lon = AddSlashes($lon);

		$where = array(
			"sw_latitude <= '{$enc_lat}'",
			"ne_latitude >= '{$enc_lat}'",
			"sw_longitude <= '{$enc_lon}'",
			"ne_longitude >= '{$enc_lon}'",
		);

		$where = implode(" AND ", $where);

		$sql = "SELECT * FROM Timezones WHERE {$where}";
		$rsp = db_fetch($sql);

		if (! $rsp['ok']){
			return $rsp;
		}

		if (! $point_in_poly){
			return $rsp;
		}

		if (count($rsp['rows']) == 1){

		}

		# please so point-in-poly here...
	}

	#################################################################

	# the end
