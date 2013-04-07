<?php

	include("include/init.php");

	loadlib("youarehere_users");
	loadlib("youarehere_api");

	login_ensure_loggedin();

	features_ensure_enabled("youarehere");

	$code = get_str("code");

	if (! $code){
		error_404();
	}

	youarehere_api_get_access_token($code);

	# check scope here
	# {"access_token":"xxxx","scope":"write"}

	exit();
?>
