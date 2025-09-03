<?php
	include("include/init.php");

	loadlib("passwords_reset");

	login_ensure_loggedout();

	if (! $GLOBALS['cfg']['enable_feature_password_retrieval']){
		error_404();
	}

	$reset_code = get_str('reset');

	if (! $reset_code){

		# seriously, go away...

		header("location: /");
		exit();
	}

	$user = passwords_reset_get_user_with_code($reset_code);

	if (! $user){

		$smarty->assign('error_nouser', 1);
		$smarty->display('page_reset.txt');
		exit();	
	}

	$smarty->assign('reset_code', $reset_code);

	if (post_isset('done')){

		$new_password1 = post_str('new_password1');
		$new_password2 = post_str('new_password2');

		if ((! $new_password1) || (! $new_password2)){

			$smarty->assign('error_missing_password', 1);
			$smarty->display('page_reset.txt');
			exit();	
		}

		if ($new_password1 !== $new_password2){

			$smarty->assign('error_password_mismatch', 1);
			$smarty->display('page_reset.txt');
			exit();	
		}

		if (! users_update_password($user, $new_password1)){

			$smarty->assign('error_update_failed', 1);
			$smarty->display('page_reset.txt');
			exit();	
		}

		passwords_reset_purge_codes_for_user($user);

		$user = users_get_by_id($user['id']);

		login_do_login($user, "/account?password=1");
		exit();	
	}


	#
	# output
	#

	$smarty->display('page_reset.txt');
	exit();