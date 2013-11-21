<?php

	include("include/init.php");

	login_ensure_loggedin();

	$create_crumb = crumb_generate("api", "privatesquare.venues.create");	
	$GLOBALS['smarty']->assign("create_crumb", $create_crumb);

	$GLOBALS['smarty']->display("page_privatesquare_create.txt");
	exit();
?>
