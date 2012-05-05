<?php

	include("include/init.php");

	loadlib("users_preferences");

	$prefs = users_preferences_for_user($GLOBALS['cfg']['user']);
	dumper($prefs);
	exit();
?>
