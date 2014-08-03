<?php

	include("include/init.php");
	login_ensure_loggedin();

	$email = $GLOBALS['cfg']['user']['email'];
	list($name, $domain) = explode("@", $email, 2);

	$first = substr($name, 0, 1);
	$last = substr($name, strlen($name) - 1, 1);

	$parts = array();

	foreach (explode(".", $domain) as $part){
		$parts[] .= substr($part, 0, 1) . "***";
	}

	$parts  = implode(".", $parts);

	$obfuscated  = "{$first}***{$last}@{$parts}";

	$GLOBALS['cfg']['user']['email_obfuscated'] = $obfuscated;

	$GLOBALS['smarty']->display("page_account.txt");
	exit();

?>
