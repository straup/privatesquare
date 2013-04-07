<?php

	include("include/init.php");

	loadlib("youarehere_users");
	loadlib("youarehere_api");

	login_ensure_loggedin();

	features_ensure_enabled("youarehere");

	exit();
?>
