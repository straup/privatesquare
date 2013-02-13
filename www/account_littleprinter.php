<?php

	include("include/init.php");

	login_ensure_loggedin();

	$GLOBALS['smarty']->display("page_account_littleprinter.txt");
	exit();

?>	
