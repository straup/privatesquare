<?php

	include("include/init.php");

	# This is probably to creepy of a leaky privacy fuckup
	# waiting to happen so it's just stubbed out and disabled
	# for the time being... (20131117/straup)

	header("location: /");
	exit();

	login_ensure_loggedin();

	$owner = $GLOBALS['cfg']['user'];
	$GLOBALS['smarty']->assign_by_ref("owner", $owner);

	$provider = get_str("provider");

	if (! $provider){
		error_404();
	}

	$provider_id = venues_providers_label_to_id($provider);

	if (! venues_providers_is_valid_provider($provider_id)){
		error_404();
	}

	$more = array();

	if ($page = get_int32("page")){
		$more['page'] = $page;
	}

	$rsp = venues_get_by_provider($provider_id, $more);
	$venues = $rsp['rows'];

	$GLOBALS['smarty']->assign("provider", $provider);
	$GLOBALS['smarty']->assign_by_ref("venues", $venues);

	$GLOBALS['smarty']->display("page_venues_provider.txt");
	exit();

?>
