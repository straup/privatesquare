<?php

	########################################################################

	function youarehere_users_add_user($youarehere_user){

		$youarehere_user['created'] = time();

		$insert = array();

		foreach ($youarehere_user as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert('YouarehereUsers', $insert);

		if ($rsp['ok']){
			$rsp['youarehere_user'] = $youarehere_user;
		}

		return $rsp;
	}

	########################################################################

	function youarehere_users_update_user($youarehere_user, $update){

		$update['last_modified'] = time();

		$insert = array();

		foreach ($update as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$enc_id = AddSlashes($youarehere_user['user_id']);
		$where = "user_id='{$enc_id}'";

		$rsp = db_update('YouarehereUsers', $insert, $where);

		if ($rsp['ok']){
			$youarehere_user = array_merge($youarehere_user, $update);
			$rsp['youarehere_user'] = $youarehere_user;
		}

		return $rsp;
	}

	########################################################################

	function youarehere_users_delete_user($youarehere_user){

		$enc_id = AddSlashes($youarehere_user['user_id']);
		$sql = "DELETE FROM YouarehereUsers WHERE user_id='{$enc_id}'";

		$rsp = db_write($sql);
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
