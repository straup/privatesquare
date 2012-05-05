<?php

	include("include/init.php");

	loadlib("users_preferences");

	$rsp = users_preferences_for_user($GLOBALS['cfg']['user']);
	$prefs = $rsp['preferences'];

	dumper($prefs);
exit;

	$prefs['foursquare_broadcast'] = 3;

dumper($prefs);

	$rsp = users_preferences_update($GLOBALS['cfg']['user'], $prefs);
	dumper($rsp);
	exit();
?>
