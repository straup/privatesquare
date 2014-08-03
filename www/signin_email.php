<?php
	include("include/init.php");

	features_ensure_enabled("signin");

	login_ensure_loggedout();

	$redir = request_str('redir');
	$GLOBALS['smarty']->assign('redir', $redir);

	if (post_str('signin')){

		$email		= post_str('email');
		$password	= post_str('password');

		$GLOBALS['smarty']->assign('email', $email);

		$ok = 1;

		if ((!strlen($email)) || (!strlen($password))){

			$GLOBALS['smarty']->assign('error_missing', 1);
			$ok = 0;
		}

		if ($ok){
			$user = users_get_by_email($email);

			if (!$user['id']){

				$GLOBALS['smarty']->assign('error_nouser', 1);
				$ok = 0;
			}
		}

		if ($ok && $user['deleted']){

			$GLOBALS['smarty']->assign('error_deleted', 1);
			$ok = 0;
		}

		if ($ok){

			if (! passwords_validate_password_for_user($password, $user)){
				$GLOBALS['smarty']->assign('error_password', 1);
				$ok = 0;
			}
		}

		if ($ok){
			$redir = ($redir) ? $redir : '/';

			login_do_login($user, $redir);
			exit;
		}
	}

	$GLOBALS['smarty']->display('page_signin.txt');
	exit();

?>
