<?php

	include("include/init.php");
	loadlib("trips");

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];
	$more = array();

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = trips_get_for_user($user, $more);

	$GLOBALS['smarty']->assign_by_ref("trips", $rsp['rows']);
	
	$GLOBALS['smarty']->display("page_user_trips.txt");
	exit();

?>
