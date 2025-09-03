<?php

	#################################################################

	# a helper function which performs password hash promotion when a hash
	# is not yet bcrypt and we're configured to allow it.

	function passwords_utils_validate_password_for_user($password, &$user){

		#
		# is this is *not* a bcrypt hash, but we allow promotion,
		# then verify & promote it.
		#

		$is_bcrypt = substr($user['password'], 0, 4) == '$2a$';

		if ($GLOBALS['cfg']['passwords_use_bcrypt'] && $GLOBALS['cfg']['passwords_allow_promotion'] && !$is_bcrypt){

			$test = hash_hmac("sha256", $password, $GLOBALS['cfg']['crypto_password_secret']);

			$is_ok = $test == $user['password'];

			if ($is_ok){

				if (users_update_password($user, $password)){
					$user = users_get_by_id($user['id']);
				}
			}

			return $is_ok;
		}

		return passwords_validate_password($password, $user['password']);
	}

	#################################################################
	# the end