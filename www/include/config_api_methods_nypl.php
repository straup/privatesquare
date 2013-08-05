<?php

	########################################################################

	$GLOBALS['cfg']['api']['methods'] = array_merge(array(

		"nypl.gazetteer.search" => array(
			"documented" => 0,
			"enabled" => 1,
			"requires_auth": 1,
			"library" => "api_nypl_gazetteer"
		),

	), $GLOBALS['cfg']['api']['methods']);

	########################################################################

	# the end
