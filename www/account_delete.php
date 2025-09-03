<?php
	include("include/init.php");

	if ($GLOBALS['cfg']['users_use_module'] != "flamework"){
		error_404();
	}

	login_ensure_loggedin();

	if ($GLOBALS['cfg']['users_use_module'] != "flamework"){
		error_404();
	}

	#
	# generate a crumb
	#

	$crumb_key = 'account_delete';
	$smarty->assign('crumb_key', $crumb_key);


	#
	# delete account?
	#

	if (post_str('delete') && crumb_check($crumb_key)){

		if (post_str('confirm')){

			$ok = users_delete_user($GLOBALS['cfg']['user']);

			if ($ok){
				login_do_logout();

				$smarty->display('page_account_delete_done.txt');
				exit;
			}

			$smarty->assign('error_deleting', 1);

			$smarty->display('page_account_delete.txt');
			exit;
		}

		$smarty->display('page_account_delete_confirm.txt');
		exit;
	}


	#
	# output
	#

	$smarty->display("page_account_delete.txt");
