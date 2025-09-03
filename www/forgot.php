<?php
	include("include/init.php");

	loadlib("passwords_reset");

	if (! $GLOBALS['cfg']['enable_feature_password_retrieval']){
		error_404();
	}

	login_ensure_loggedout();

	$crumb_key = 'forgot';
	$smarty->assign("crumb_key", $crumb_key);

	#
	# send the reminder?
	#

	if (post_str('remind') && crumb_check($crumb_key)){

		$email	= post_str('email');
		$user	= users_get_by_email($email);

		$ok = 1;

		if (!$user){

			$smarty->assign('error_nouser', 1);
			$ok = 0;
		}

		if ($ok && $user['deleted']){

			$smarty->assign('error_deleted', 1);
			$ok = 0;
		}

		if ($ok && !passwords_reset_send_code_to_user($user)){

			$smarty->assign('error_notsent', 1);
			$ok = 0;
		}

		if ($ok){
			$smarty->assign('sent_to', $user['email']);

			$smarty->display('page_forgot_sent.txt');
			exit;
		}
	}


	#
	# output
	#

	$smarty->display('page_forgot.txt');
