<?php

	########################################################################

	function youarehere_users_add_user($user){

		$user['created'] = time();

		$insert = array();

		foreach ($user as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert('YouarehereUsers', $insert);

		if ($rsp['ok']){
			$rsp['user'] = $user;
		}

		return $rsp;
	}

	########################################################################

	function youarehere_users_get_by_user_id($user_id){

		$enc_id = AddSlashes($user_id);

		$sql = "SELECT * FROM YouarehereUsers WHERE user_id='{$enc_id}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	########################################################################

	# the end
