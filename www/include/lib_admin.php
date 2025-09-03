<?php

	echo "POO";

	########################################################################

	function admin_ensure_admin(){

		login_ensure_loggedin();

		$user = $GLOBALS["cfg"]["user"];

		if (! users_roles_has_role($user, "admin")){
			error_403();
		}

	}

	########################################################################

	# the end