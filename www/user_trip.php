<?php

	include("include/init.php");
	loadlib("trips");

	features_ensure_enabled("trips");

	login_ensure_loggedin();

	# in advance of a proper fix... (20140125/straup)
	$GLOBALS['cfg']['pagination_assign_smarty_variable'] = 0;

	$user = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $user);

	if ($id = get_int64("trip_id")){
		$trip = trips_get_by_id($id);
	}

	elseif ($id = get_int32("dopplr_id")){
		$trip = trips_get_by_dopplr_id($id);
	}

	else {
		error_404();
	}

	if (! $trip){
		error_404();
	}

	if ($trip['user_id'] != $user['id']){
		error_403();
	}

	trips_inflate_trip($trip);

 	$GLOBALS['smarty']->assign_by_ref("trip", $trip);

	$loc = $trip['locality'];
	$GLOBALS['smarty']->assign_by_ref("locality", $loc);

      	$tr_more = array();

        $tr_more['where'] = $loc['place_type'];
        $tr_more['woeid'] = $loc['woeid'];
	$tr_more['exclude_trip'] = $trip['id'];
	# $tr_more['per_page'] = 5;

        $tr_rsp = trips_get_for_user($user, $tr_more);
        $other_trips = array();

        foreach ($tr_rsp['rows'] as $row){
		trips_inflate_trip($row);
                $other_trips[] = $row;
        }

 	$GLOBALS['smarty']->assign_by_ref("other_trips", $other_trips);
 	$GLOBALS['smarty']->assign_by_ref("other_trips_pagination", $tr_rsp['pagination']);

	$ch_more = array(
		'locality' => $loc['woeid'],
	);

	$ch_rsp = privatesquare_checkins_venues_for_user($user, $ch_more);
	$GLOBALS['smarty']->assign_by_ref("venues", $ch_rsp['rows']);
	$GLOBALS['smarty']->assign_by_ref("venues_pagination", $ch_rsp['pagination']);

	# Check to see if there are any checkins during this trip

	if (count($ch_rsp['rows'])){

		$when = implode(";", array(
			$trip['arrival'],
			$trip['departure'],
		));

		$ch_more['when'] = $when;

		$ch_more['between'] = array(
			'start' => $trip['arrival'],
			'end' => $trip['departure'],
		);

		$ch_rsp2 = privatesquare_checkins_venues_for_user($user, $ch_more);
		$GLOBALS['smarty']->assign_by_ref("checkins", $ch_rsp2['rows']);
		$GLOBALS['smarty']->assign_by_ref("checkins_pagination", $ch_rsp2['pagination']);
	}

	if ((count($ch_rsp['rows'])) && (! $trip['departure_past'])){

		$atlas = array();

		$yes = array("i want to go there", "again again", "again");
		$no = array("again maybe", "again never", "meh");

		$status_map = privatesquare_checkins_status_map('string keys');

		foreach (array_merge($yes, $no) as $status){

			$status_id = $status_map[$status];

			$at_more = array(
				'locality' => $trip['locality_id'],
			);

			$at_rsp = privatesquare_checkins_venues_for_user_and_status($user, $status_id, $at_more);

			foreach ($at_rsp['rows'] as &$row){
				$venue = json_decode($row['venue']['data'], 'as hash');
				$row['venue']['address'] = $venue['location']['address'];
			}

			$atlas[$status] = $at_rsp;
		}

		$GLOBALS['smarty']->assign_by_ref("atlas_yes", $yes);
		$GLOBALS['smarty']->assign_by_ref("atlas_no", $no);
		$GLOBALS['smarty']->assign_by_ref("atlas", $atlas);
	}

	# TO DO: sort out pagination nonsense... (20140120/straup)

	$travel_map = trips_travel_type_map();
	$GLOBALS['smarty']->assign_by_ref("travel_map", $travel_map);

	$status_map = trips_travel_status_map();
	$GLOBALS['smarty']->assign_by_ref("status_map", $status_map);

	$edit_crumb = crumb_generate("api", "privatesquare.trips.editTrip");
	$GLOBALS['smarty']->assign("edit_crumb", $edit_crumb);

	$delete_crumb = crumb_generate("api", "privatesquare.trips.deleteTrip");
	$GLOBALS['smarty']->assign("delete_crumb", $delete_crumb);

	$GLOBALS['smarty']->display("page_user_trip.txt");
	exit();

?>
