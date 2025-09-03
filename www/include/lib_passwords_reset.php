<?php

	#################################################################

	function passwords_reset_get_user_with_code($code){

		$row = passwords_reset_get_with_code($code);

		if (! $row){
			return null;
		}

		$now = time();
		$ttl = $GLOBALS['cfg']['password_retrieval_ttl'];

		if ($now >= $row["created"] + $ttl){

			passwords_reset_delete_code($code);
			return null;
		}

		return users_get_by_id($row["user_id"]);
	}

	#################################################################

	function passwords_reset_get_with_code($code){

		$enc_code = AddSlashes($code);
		$sql = "SELECT * FROM users_password_reset WHERE reset_code='{$enc_code}'";

		$rsp = db_fetch_accounts($sql);
		return db_single($rsp);
	}

	#################################################################

	function passwords_reset_send_code_to_user(&$user){

		$code = passwords_reset_generate_code($user);

		if (!$code) {
			return 0;
		}

		$GLOBALS['smarty']->assign('code', $code);

		email_send(array(
			'to_email'	=> $user['email'],
			'template'	=> 'email_password_reset.txt',
		));

		return 1;
	}

	#################################################################

	function passwords_reset_generate_code(&$user){

		loadlib('random');

		passwords_reset_purge_codes_for_user($user);

		$code = '';

		while (!$code){

			$code = random_string(32);
			$enc_code = AddSlashes($code);

			$sql = "SELECT 1 FROM users_password_reset WHERE reset_code='{$enc_code}'";
			$rsp = db_fetch_accounts($sql);

			if (db_single($rsp)){
				$code = '';
			}

			break;
		}

		$rsp = db_insert_accounts('users_password_reset', array(
			'user_id'	=> $user['id'],
			'reset_code'	=> $enc_code,
			'created'	=> time(),
		));

		if (!$rsp['ok']){
			return null;
		}

		return $code;
	}

	#################################################################

	function passwords_reset_purge_codes_for_user(&$user){

		$enc_user = intval($user["id"]);
		$sql = "DELETE FROM users_password_reset WHERE user_id=$enc_user";

		$rsp = db_write_accounts($sql);

		return $rsp['ok'];
	}

	#################################################################

	function passwords_reset_delete_code($code){

		$enc_code = AddSlashes($code);
		$sql = "DELETE FROM users_password_reset WHERE reset_code = '{$enc_code}'";

		$rsp = db_write_accounts($sql);
		return $rsp['ok'];
	}

	#################################################################

	# the end	