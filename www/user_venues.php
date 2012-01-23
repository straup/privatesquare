<?php

	include("include/init.php");
	login_ensure_loggedin();

	loadlib("privatesquare_checkins");

	$rsp = privatesquare_checkins_venues_for_user($GLOBALS['cfg']['user']);
	dumper($rsp);
?>
