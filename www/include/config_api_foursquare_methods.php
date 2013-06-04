<?php

	########################################################################

	$GLOBALS['cfg']['api']['methods'] = array_merge(array(

		"foursquare.venues.search" => array (
			"description" => "",
			"documented" => 0,
			"enabled" => 1,
			"library" => "api_foursquare_venues"
		),

	), $GLOBALS['cfg']['api']['methods']);

	########################################################################

	# the end
