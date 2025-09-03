<?php

	########################################################################

	function users_roles_has_role(&$user, $role){

		$roles = users_roles_get_roles($user);
		return in_array($role, $roles);
	}

	########################################################################

	function users_roles_get_roles(&$user){

		if (! $user['id']){
			return array();
		}

		$esc_user_id = AddSlashes($user['id']);

		$rsp = db_fetch("SELECT user_role FROM users_roles WHERE user_id='{$esc_user_id}'");

		if (! $rsp['ok']) {
			// So here maybe I should raise more of a fuss, in case
			// the database table hasn't been added or something?
			// For now it just quietly says "nope."
			// (20161212/dphiffer)
			return array();
		}

		$roles = array();

		foreach ($rsp['rows'] as $row) {
			$roles[] = $row['user_role'];
		}

		return $roles;
	}

	########################################################################

	function users_role_grant_role(&$user, $role) {

		$esc_user_id = AddSlashes($user['id']);
		$esc_role = AddSlashes($role);

		$insert = array(
			'user_id' => $esc_user_id,
			'user_role' => $esc_role
		);

		$rsp = db_insert('users_roles', $insert);
		return $rsp;
	}

	########################################################################

	# the end