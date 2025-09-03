<?php
	include("include/init.php");

	if ($GLOBALS['cfg']['users_use_module'] != "flamework"){
		error_404();
	}

	login_ensure_loggedin();

	#
	# output
	#

	$smarty->display("page_account.txt");
