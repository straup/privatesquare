<?php

	# Note: this is not stock flamework code (20130618/straup)

	########################################################################

	function auth_has_role($role, $who=0){

		$who = ($who) ? $who : $GLOBALS['cfg']['user']['id'];

		if (! $who){
			return 0;
		}

		if (! isset($GLOBALS['cfg']['auth_users'][$who])){
			return 0;
		}

		$details = $GLOBALS['cfg']['auth_users'][$who];
		$roles = $details['roles'];

		return (in_array($role, $roles)) ? 1 : 0;
	}

	########################################################################

	# the end
