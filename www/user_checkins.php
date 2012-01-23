<?php

	include("include/init.php");
	login_ensure_loggedin();

	loadlib("privatesquare_checkins");

	$more = array(
		'page' => get_int32('page'),
	);

	$rsp = privatesquare_checkins_for_user($GLOBALS['cfg']['user'], $more);
	dumper($rsp);

?>
