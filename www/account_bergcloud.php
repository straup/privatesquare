<?php

	include("include/init.php");

	features_ensure_enabled("bergcloud_users");

	login_ensure_loggedin();

	loadlib("bergcloud_users");

	$crumb_key = 'berg';
	$GLOBALS['smarty']->assign("crumb_key", $crumb_key);

	$berg_user = bergcloud_users_get_by_user_id($GLOBALS['cfg']['user']['id']);

	if (post_isset('update') && crumb_check($crumb_key)){

		$code = post_str("direct_print_code");
		$lp_updates = (post_str("littleprinter_updates")) ? 1 : 0;

		$update = array(
			'direct_print_code' => $code,
			'littleprinter_updates' => $lp_updates,
		);

		if ($berg_user){
			$rsp = bergcloud_users_update_user($berg_user, $update);
		}

		else {
			$update['user_id'] = $GLOBALS['cfg']['user']['id'];
			$rsp = bergcloud_users_add_user($update);
		}

		if ($rsp['ok']){
			$berg_user = $rsp['user'];
		}

		$GLOBALS['smarty']->assign_by_ref("update", $rsp);
	}

	$GLOBALS['smarty']->assign_by_ref("berg_user", $berg_user);

	$GLOBALS['smarty']->display("page_account_bergcloud.txt");
	exit();

?>	
