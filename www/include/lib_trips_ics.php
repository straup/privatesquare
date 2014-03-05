<?php

	########################################################################

	function trips_ics_export(&$trips, $fh, $more=array()){

		foreach ($trips as $row){
			trips_ics_export_row($row, $fh, $more);
		}
	}

	########################################################################

	function trips_ics_export_row($row, $fh, $more=array()){

		$vevent = trips_ics_to_vevent($row);

		$GLOBALS['smarty']->assign_by_ref("vevent", $vevent);
		$ics = $GLOBALS['smarty']->fetch("page_export_ics_vevent.txt");

		fwrite($fh, $ics);
	}

	########################################################################

	# This is probably still wrong (20140305/straup)

	function trips_ics_to_vevent(&$trip){

		$event = array(
			'id' => $trip['id'],
			'created' => $trip['created'],
			'name' => 'trip',
			'latitude' => $trip['latitude'],
			'longitude' => $trip['longitude'],
		);

		if ($trip['locality']){
			$event['name'] = $trip['locality']['woe_name'];
			$event['location'] = $trip['locality']['name'];
		}

		return $event;
	}

	########################################################################

	# the end
