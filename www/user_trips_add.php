<?php

	include("include/init.php");
	loadlib("trips");

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("user", $user);
	
	$travel_map = trips_travel_type_map();
	$GLOBALS['smarty']->assign_by_ref("travel_map", $travel_map);

	$GLOBALS['smarty']->display("page_user_trips_add.txt");
	exit();

?>
