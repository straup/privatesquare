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

	function bergcloud_users_add($user){

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

	########################################################################
