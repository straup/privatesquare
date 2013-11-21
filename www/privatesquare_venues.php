<?php

	include("include/init.php");

	login_ensure_loggedin();

	$more = array();

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$user = $GLOBALS['cfg']['user'];
	$rsp = venues_privatesquare_get_for_user($user, $more);

	$venues = $rsp['rows'];
	$GLOBALS['smarty']->assign_by_ref("venues", $venues);

	$GLOBALS['smarty']->display("page_privatesquare_venues.txt");
	exit();
?>
