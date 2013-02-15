<?php

	########################################################################

	function bergcloud_users_get_by_user_id($id){

		$enc_id = AddSlashes($id);

		$sql = "SELECT * FROM BergcloudUsers WHERE user_id='{$enc_id}'";

		$rsp = db_fetch($sql);
		$row = db_single($rsp);

		return $row;
	}

	########################################################################

	function bergcloud_users_add_user($user){

		$insert = array();

		foreach ($user as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$rsp = db_insert('BergcloudUsers', $insert);

		if ($rsp['ok']){
			$rsp['user'] = $user;
		}

		return $rsp;
	}

	########################################################################

	function bergcloud_users_update_user(&$berg_user, $update){

		$insert = array();

		foreach ($update as $k => $v){
			$insert[$k] = AddSlashes($v);
		}

		$enc_id = AddSlashes($berg_user['user_id']);
		$where = "user_id='{$enc_id}'";

		$rsp = db_update('BergcloudUsers', $insert, $where);

		if ($rsp['ok']){
			$berg_user = array_merge($berg_user, $update);
			$rsp['user'] = $berg_user;
		}

		return $rsp;
	}

	########################################################################
