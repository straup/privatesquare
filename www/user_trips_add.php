<?php

	include("include/init.php");
	loadlib("trips");

	login_ensure_loggedin();

	$user = $GLOBALS['cfg']['user'];

	$GLOBALS['smarty']->assign_by_ref("user", $user);
	
	$GLOBALS['smarty']->display("page_user_trips_add.txt");
	exit();

?>
