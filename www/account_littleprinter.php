<?php

	include("include/init.php");

	login_ensure_loggedin();

	loadlib("bergcloud_users");

	$berg_user = bergcloud_users_get_by_user_id($GLOBALS['cfg']['user']['id']);
	$GLOBALS['smarty']->assign_by_ref("berg_user", $berg_user);

	$crumb_key = 'berg';
	$GLOBALS['smarty']->assign("crumb_key", $crumb_key);

	if (post_isset('update') && crumb_check($crumb_key)){

		$update = array();

		if ($code = post_str("direct_print_code")){

			$update['direct_print_code'] = $code;

			if ($berg_user){

			}

			else {
				$update['user_id'] = $GLOBALS['cfg']['user']['id'];
				$rsp = bergcloud_users_add($update);
			}
		}
	}

	$GLOBALS['smarty']->display("page_account_littleprinter.txt");
	exit();

?>	
