<?php

	########################################################################

	$GLOBALS['cfg']['api']['methods'] = array_merge(array(

		"privatesquare.venues.checkin" => array (
			"description" => "",
			"documented" => 0,
			"enabled" => 1,
			"requires_auth" => 1,
			"requires_crumb" => 1,
			"crumb_ttl" => 1200,
			"library" => "api_privatesquare_venues"
		),

		"privatesquare.checkins.delete" => array(
			"description" => "",
			"documented" => 1,
			"enabled" => 1,
			"requires_auth" => 1,
			"requires_crumb" => 1,
			"crumb_ttl" => 600,
			"library" => "api_privatesquare_checkins"
		),

		"privatesquare.checkins.updateStatus" => array(
			"description" => "",
			"documented" => 0,
			"enabled" => 1,
			"requires_auth" => 1,
			"requires_crumb" => 1,
			"crumb_ttl" => 600,
			"library" => "api_privatesquare_checkins"
		),

	), $GLOBALS['cfg']['api']['methods']);

	########################################################################

	# the end
