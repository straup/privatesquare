<?php
	/*

	How to configure ACLs:

	This goes in your config_local.php

	$GLOBALS['cfg']['users_acl'] = array(
		'staff' => array(
			'can_upload'
		),
	);

	*/

	loadlib("users");
	loadlib("users_roles");

	########################################################################

	function users_acl_has_capability($user, $capability){

		if (! preg_match('/^[a-z0-9_*-]+$/i', $capability)) {
			error_log("users_acl_check_access: invalid capability '$capability'");
			return false;
		}

		$roles = users_roles_get_roles($user);

		$capabilities = users_acl_get_capabilities($roles);

		if (in_array($capability, $capabilities)){
			return true;
		}

		foreach ($capabilities as $test){

			if (! preg_match('/^[a-z0-9_*-]+$/i', $test)) {
				error_log("users_acl_check_access: invalid capability '$test'");
				return false;
			}

			if (fnmatch($test, $capability)) {
				return true;
			}
		}

		return false;
	}


	########################################################################

	function users_acl_get_capabilities($user_roles){

		$capabilities = array();

		foreach ($GLOBALS['cfg']['users_acl'] as $role => $caps){

			if (in_array($role, $user_roles)){
				$capabilities = array_merge($capabilities, $caps);
			}
		}

		return $capabilities;
	}

	########################################################################

	# the end
